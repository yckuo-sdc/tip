<?php

require_once __DIR__ .'/../../vendor/autoload.php';
// Sanitizes data and converts strings to UTF-8 (if available), according to the provided field whitelist
$whitelist = array("query");
$_GET = $gump->sanitize($_GET, $whitelist);

foreach ($_POST as $postKey => $val) {
    $$postKey = $val;
}

if (empty($query)) {
    echo "No input";
    return 0;
}


$intelSources = ['shodan'];
$intel = new IntelligenceAdapter();

$intelResults = array();
foreach ($intelSources as $intelSource) {
    $intelResults[$intelSource] = $intel->search($query, $intelSource);
}

#var_dump($intelResults);
echo "Results";
$jsonString = json_encode($intelResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$render = "<pre style='white-space: pre-wrap'>" . $jsonString . "</pre>";
$flash->success($render);

echo $twig->render('ajax/do_search.html', ['intel_results' => $intelResults, 'flash' => $flash]);
