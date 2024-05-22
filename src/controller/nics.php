<?php

if(!$userValidator->isLogin()) {
    header("Location: /logout");
    return;
}

$userAction->logger('pageSwitch', $_SERVER['REQUEST_URI']);

$subpage = strtolower($route->getParameter(2));
$controller_array = scandir('../src/controller/nics');
$controller_array = array_change_key_case($controller_array, CASE_LOWER);

if (in_array($subpage.'.php', $controller_array)) {
    require '../src/controller/nics/'.$subpage.'.php';
} else {
    require '../src/controller/nics/search.php';
}
