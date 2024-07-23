<?php
$params = [
    #'scroll' => '5m', // period to retain the search context
    'index' => 'gsn_asset*',
    'from' => 0,
    'size' => 10000,
    'body'  => [
        'query' => [
            'bool' => [
                #'match_all' => new stdClass(),
                'filter' => [
                    'range' => [
                        'Update_Month' => [
                            'gte' => 'now-60d/d',
                            'lte' => 'now/d'
                        ]
                    ]
                ]
            ]
        ],
        'sort' => [
            '_id' => [
                'order' => 'desc'
            ]
        ]
    ]
];

$response = $es_client->search($params);


// Initialize an array to hold the _source fields
$source_array = [];

// Loop through each item in the data and extract the _source field
foreach ($response['hits']['hits'] as $item) {
    if (isset($item['_source'])) {
        $source_array[] = $item['_source'];
    }
}


$data = array();
$data['data'] = $source_array;

echo json_encode($data, JSON_UNESCAPED_UNICODE);
