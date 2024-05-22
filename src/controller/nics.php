<?php

if(!$userValidator->isLogin()) {
    header("Location: /logout");
    return;
}

$userAction->logger('pageSwitch', $_SERVER['REQUEST_URI']);

$subpage = strtolower($route->getParameter(2));
$controller_array = scandir(SRC_PATH . 'controller/nics');
$controller_array = array_change_key_case($controller_array, CASE_LOWER);

if (in_array($subpage.'.php', $controller_array)) {
    require SRC_PATH . 'controller/nics/'.$subpage.'.php';
} else {
    require SRC_PATH . 'controller/nics/search.php';
}
