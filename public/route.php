<?php

$mainpage = strtolower($route->getParameter(1));    // 用參數決定載入某頁並讀取需要的資料
$controller_array = scandir(SRC_PATH . 'controller');
$controller_array = array_change_key_case($controller_array, CASE_LOWER);

if (in_array($mainpage . '.php', $controller_array)) {
    require SRC_PATH . 'controller/' . $mainpage . '.php';
} else {
    require SRC_PATH . 'controller/nics.php';
}
