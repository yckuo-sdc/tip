<?php

// Sanitizes data and converts strings to UTF-8 (if available), according to the provided field whitelist
$whitelist = array("query");
$params = $gump->sanitize($_POST, $whitelist);

if (empty($params)) {
    echo "No input";
    return 0;
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host . "/nics/search/";

$url_with_params = $base_url . '?' . http_build_query($params);
echo $url_with_params;

header("Location: " . $url_with_params);
