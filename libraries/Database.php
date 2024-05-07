<?php

class Database
{
    private static $instance;
    private function __construct()
    {
        // 使用 private 建構子避免在外面被意外地初始化
    }

    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new DatabaseAccessObject(
                MySQL::HOST,
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
require 'database.php';
$db = Database::get();

// execute
$sql = "SELECT * FROM logs WHERE user LIKE 'yckuo' ";
$entries= $db->execute($sql, []);
print_r($db->getLastSql());
print_r($db->getErrorMessage());

// select
$table = "security_event"; // 設定你想查詢資料的資料表
$condition = "IP LIKE :IP ";
$data_array[':IP'] = "%192.168.112%";
$log = $db->query($table, $condition, $order_by = "1", $fields = "*", $limit = "", $data_array);

// insert
$table = "logs"; // 設定你想新增資料的資料表
$data_array['type'] = "test";
$data_array['ip'] = "localhost";
$data_array['user'] = "kkc";
$data_array['msg'] = "";
$data_array['time'] = date("Y-m-d H:i:s");
$db->insert($table, $data_array);
$logs_id = $db->getLastId(); // 可以拿到他自動建立的 id

// update
$table = "logs";
$data_array['type'] = "test2"; // 想改他的名字
$key_column = "user";
$user = "kkc";
$db->update($table, $data_array, $key_column, $user);

// delete
$table = "logs";
$key_column = "user";
$user = "kkc";
$db->delete($table, $key_column, $user);

**/
