<?php

require_once __DIR__ . '/../vendor/autoload.php';

$db = Database::get();
$ip = Ip::get();

$table = "drip_client_list";
$condition = "IP LIKE :IP ";
$data_array[':IP'] =  $ip;
$entries = $db->query($table, $condition, $order_by = "1", $fields = "MAC", $limit = "", $data_array);

$mac = "";
if (!empty($entries)) {
    $mac = $entries[0]['MAC'];
}

$response = ['ip' => $ip, 'mac' => $mac];
echo json_encode($response, JSON_UNESCAPED_UNICODE);
