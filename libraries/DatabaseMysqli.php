<?php

class DatabaseMysqli
{
    private static $instance;
    private function __construct()
    {
        // 使用 private 建構子避免在外面被意外地初始化
    }

    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new DatabaseAccessObjectMysqli(
                MySQL::ADDRESS,
                MySQL::USERNAME,
                MySQL::PASSWORD,
                MySQL::DATABASE
            );
        }
    }

    public static function get()
    {
        self::getInstance();
        if (isset(self::$instance)) {
            return self::$instance;
        } else {
            return null;
        }
    }

    public static function unlinkDAO()
    {
        if (isset(self::$instance)) {
            self::$instance = null; // 會自動執行解構子 close link
        }
    }
}

/** Usage
require 'DatabaseMysqli.php';

$db = DatabaseMysqli::get();

// insert
$table = "logs"; // 設定你想新增資料的資料表
$data_array['type'] = "test";
$data_array['ip'] = "localhost";
$data_array['user'] = "kkc";
$data_array['msg'] = "";
$data_array['time'] = date("Y-m-d H:i:s");
$db->insert($table, $data_array);
$logs_id = $db->getLastId(); // 可以拿到他自動建立的 id
echo $logs_id;

// select
$table = "logs"; // 設定你想查詢資料的資料表
$condition = "user = 'kkc'";
$log = $db->query($table, $condition, $order_by = "1", $fields = "*", $limit = "");
//$log = $db->execute("SELECT * FROM logs WHERE user = 'kkc'");
print_r($log);

/*
// update
$table = "logs";
$data_array['type'] = "test2"; // 想改他的名字
$key_column = "id"; //
$id = $logs_id; // 根據我們剛剛上面拿到的 hero ID
$db->update($table, $data_array, $key_column, $id);
echo $db->getLastSql();

// delete
$table = "logs";
$key_column = "user";
$user = "kkc";
$db->delete($table, $key_column, $user);
**/
