<?php

// Sanitizes data and converts strings to UTF-8 (if available), according to the provided field whitelist
$whitelist = array("query");
$params = $gump->sanitize($_POST, $whitelist);

if (empty($params)) {
    echo "No input";
    return 0;
}

foreach ($params as $key => $val) {
    $$key = $val;
}

$intelSources = ['shodan'];
$intel = new IntelligenceAdapter();

$intelResults = array();
foreach ($intelSources as $intelSource) {
    $intelResults[$intelSource] = $intel->search($query, $intelSource);
}

#var_dump($intelResults);
echo "Raw results";
$jsonString = json_encode($intelResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$render = "<pre style='white-space: pre-wrap'>" . $jsonString . "</pre>";
$flash->success($render);

echo $twig->render('ajax/do_search.html', ['intel_results' => $intelResults, 'flash' => $flash]);
