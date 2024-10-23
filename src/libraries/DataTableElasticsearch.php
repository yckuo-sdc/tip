<?php
use Elasticsearch\ClientBuilder;

class DataTableElasticsearch
{
    private $client;
    private $index;

    public function __construct($index)
    {
        $this->client = ClientBuilder::create()
            ->setHosts([$_ENV['Elasticsearch_HOST']])
            ->build();
        $this->index = $index;
    }

    public function __destruct()
    {
        $this->client = null;
        $this->index = null;
    }

    public function handleRequest($request, $globalSearchColumns = [], $sortableColumns = [])
    {
        // Total number of documents in the index without filters
        $totalRecords = $this->getTotalRecords();

        $params = $this->buildQuery($request, $globalSearchColumns, $sortableColumns);

        $response = $this->client->search($params);

        return $this->formatResponse($response, $request['draw'], $totalRecords);
    }

    private function buildQuery($request, $globalSearchColumns = [], $sortableColumns = [])
    {
        $start = intval($request['start']);
        $length = intval($request['length']);
        $searchValue = $request['search']['value'];
        $orderColumnIndex = intval($request['order'][0]['column']);
        $orderDirection = $request['order'][0]['dir'];
        $sortField = $request['columns'][$orderColumnIndex]['data'];
        $sortField = $sortableColumns[$orderColumnIndex];

        $query = [
            'index' => $this->index,
            'body' => [
                'from' => $start,
                'size' => $length,
                'sort' => [
                    $sortField => [
                        'order' => $orderDirection
                    ]
                ],
               'query' => [
                    'bool' => [
                        'must' => []
                    ]
                ]
            ]
        ];

        if (!empty($searchValue)) {
            $query['body']['query'] = [
                'bool' => [
                    'must' => [
                        [
                            #'query_string' => [
                            #    'query' => "*" . $searchValue . "*",
                            #    'fields' => $globalSearchColumns,
                            #    'analyze_wildcard' => true,
                            #]
                            'multi_match' => [
                                'query' => $searchValue,
                                'type' => "best_fields",
                                'fields' => $globalSearchColumns,
                                'lenient' => True
                            ]
                        ]
                    ]
                ]
            ];
        }

        // Individual column search
        foreach ($request['columns'] as $col_index => $column) {
            if (!empty($column['search']['value']) && $column['searchable'] === 'true') {
                $query['body']['query']['bool']['must'][] = [
                    'match' => [$sortableColumns[$col_index] => $column['search']['value']]
                ];
            }
        }

        // If there are no search filters, use a match_all query
        if (empty($query['body']['query']['bool']['must'])) {
            $query['body']['query']['bool']['must'][] = ['match_all' => (object)[]];
        }

        return $query;

    }

    private function formatResponse($response, $draw, $totalRecords)
    {
        $data = [];
        foreach ($response['hits']['hits'] as $hit) {
            $data[] = $hit['_source'];
        }

        return json_encode([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $response['hits']['total']['value'],
            "data" => $data
        ]);
    }

    private function getTotalRecords()
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => ['match_all' => (object)[]]
            ]
        ];

        $response = $this->client->count($params);
        return $response['count'];
    }
}
