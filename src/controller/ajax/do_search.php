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

$intelSources = ['shodan', 'vt', 'censys'];
$intel = new IntelligenceAdapter();

$intelResults = array();
foreach ($intelSources as $intelSource) {
    $intelResults[$intelSource] = $intel->search($query, $intelSource);
}

// test data is added on 2024-10-25
#$intelResults = [
#    "shodan" => [
#        "count" => 1,
#        "data" => [
#            "area_code" => null,
#            "asn" => "AS395681",
#            "city" => "Los Angeles",
#            "country_code" => "US",
#            "country_name" => "United States",
#            "data" => [
#                [
#                    "_shodan" => [
#                        "crawler" => "bfb295b1dac9b1783126e88777b186a5006c26b0",
#                        "id" => "31456a30-14d0-4fd8-bf53-1c3cc410bf33",
#                        "module" => "ssh",
#                        "options" => [],
#                        "ptr" => true,
#                        "region" => "eu"
#                    ],
#                    "asn" => "AS395681",
#                    "cpe" => [
#                        "cpe:/a:openbsd:openssh:8.9p1",
#                        "cpe:/o:canonical:ubuntu_linux"
#                    ],
#                    "cpe23" => [
#                        "cpe:2.3:a:openbsd:openssh:8.9p1",
#                        "cpe:2.3:o:canonical:ubuntu_linux"
#                    ],
#                    "data" => "SSH-2.0-OpenSSH_8.9p1 Ubuntu-3ubuntu0.10\nKey type: ecdsa-sha2-nistp256\n...",
#                    "domains" => ["sugarhosts.net"],
#                    "hostnames" => ["v66-103-214.us-west.sugarhosts.net"],
#                    "info" => "protocol 2.0",
#                    "ip" => 1114101340,
#                    "ip_str" => "66.103.214.92",
#                    "isp" => "Wave 7 LLC",
#                    "location" => [
#                        "area_code" => null,
#                        "city" => "Los Angeles",
#                        "country_code" => "US",
#                        "country_name" => "United States",
#                        "latitude" => 34.05223,
#                        "longitude" => -118.24368,
#                        "region_code" => "CA"
#                    ],
#                    "org" => "Magic Particle Limited",
#                    "os" => "Linux",
#                    "port" => 22,
#                    "product" => "OpenSSH",
#                    "timestamp" => "2024-10-17T00:51:27.152584",
#                    "transport" => "tcp",
#                    "version" => "8.9p1 Ubuntu 3ubuntu0.10"
#                ]
#            ],
#            "domains" => ["sugarhosts.net"],
#            "hostnames" => ["v66-103-214.us-west.sugarhosts.net"],
#            "ip" => 1114101340,
#            "ip_str" => "66.103.214.92",
#            "isp" => "Wave 7 LLC",
#            "last_update" => "2024-10-17T00:51:27.152584",
#            "latitude" => 34.05223,
#            "longitude" => -118.24368,
#            "org" => "Magic Particle Limited",
#            "os" => "Linux",
#            "ports" => [22],
#            "region_code" => "CA",
#            "tags" => [],
#        ]
#    ],
#    "vt" => [
#        "count" => 0,
#        "data" => []
#    ],
#    "censys" => [
#        "count" => 1,
#        "data" => [
#            "ip" => "163.24.239.220",
#            "location" => [
#                "province" => "Taiwan",
#                "timezone" => "Asia/Taipei",
#                "country" => "Taiwan",
#                "continent" => "Asia",
#                "city" => "Taipei",
#                "country_code" => "TW",
#                "coordinates" => [
#                    "latitude" => 25.05306,
#                    "longitude" => 121.52639
#                ]
#            ],
#            "last_updated_at" => "2024-10-23T00:38:44.697Z",
#            "autonomous_system" => [
#                "description" => "ERX-TANET-ASN1 Taiwan Academic Network TANet Information Center",
#                "bgp_prefix" => "163.24.0.0/14",
#                "asn" => 1659,
#                "country_code" => "TW",
#                "name" => "ERX-TANET-ASN1 Taiwan Academic Network TANet Information Center"
#            ],
#            "services" => [
#                [
#                    "service_name" => "HTTP",
#                    "transport_protocol" => "TCP",
#                    "certificate" => "646522734f4eb3a039c3f815f15834ebcd55651cae34f10e0d3c851c8d7bdcd7",
#                    "extended_service_name" => "HTTPS",
#                    "port" => 443
#                ]
#            ]
#        ]
#    ]
#];

foreach (array_keys($intelResults) as $key) {
    $intelResults[$key]['rendered_data'] = renderJSON($intelResults[$key]['data']);
}


# var_dump($intelResults);
# echo "Raw results";
$jsonString = json_encode($intelResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$render = "<pre style='white-space: pre-wrap'>" . $jsonString . "</pre>";
#echo $render;
$flash->success($render);

echo $twig->render('ajax/do_search.html', ['intel_results' => $intelResults, 'flash' => $flash]);
