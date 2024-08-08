<?php

if(!$userValidator->isLogin()) {
    header("Location: ./", true, 401);
    echo "401 Unauthorized";
    return;
}

//$userAction->logger('pageSwitch', $_SERVER['REQUEST_URI']);

$admin_only_pages = array("upload_contact");
$subpage = strtolower($route->getParameter(2));
$controller_array = scandir(SRC_PATH . 'controller/ajax');
$controller_array = array_change_key_case($controller_array, CASE_LOWER);

$route_code = '404';
if (in_array($subpage . '.php', $controller_array)) {
    if (in_array($subpage, $admin_only_pages)) {
        if ($userValidator->isAdmin()) {
            $route_code = '200';
        } else {
            $route_code = '403';
        }
    } else {
        $route_code = '200';
    }
}

switch ($route_code) {
    case '200':
        require SRC_PATH . 'controller/ajax/' . $subpage . '.php';
        break;
    case '403':
        require SRC_PATH . 'controller/403.php';
        break;
    case '404':
        require SRC_PATH . 'controller/404.php';
        break;
}
