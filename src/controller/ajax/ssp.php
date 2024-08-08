<?php

$whitelist = array("draw", "start", "length", "search", "order", "columns");
$_GET = $gump->sanitize($_GET, $whitelist);

if (empty($_GET)) {
    return 0;
}

$indexName = 'gsn_asset*';
$dataTableElastic = new DataTableElasticsearch($indexName);

$globalSearchColumns = [
    'ACC.keyword',
    'Hostname.keyword',
    'IP.keyword',
    'Product.keyword',
    'Scan_Module.keyword',
    'Data_Source.keyword',
    'Data.keyword',
];

$sortableColumns = [
    //0 => null,
    1 => 'Update_Month',
    2 => 'ACC.keyword',
    3 => 'Hostname.keyword',
    4 => 'IP.keyword',
    5 => 'Port',
    6 => 'Product.keyword',
    7 => 'Scan_Module.keyword',
    8 => 'Data_Source.keyword',
];

$response = $dataTableElastic->handleRequest($_GET, $globalSearchColumns, $sortableColumns);
echo $response;
