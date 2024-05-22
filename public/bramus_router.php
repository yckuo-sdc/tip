<?php

// Create Router instance
$router = new \Bramus\Router\Router();

// Define routes
$router->get('/', function () use ($twig, $route) {
    echo $twig->render('pages/search.html', ['menu_items' => Menu::ITEM_ARRAY, 'session' => $_SESSION, 'route' => $route]);
});

$router->before('GET|POST', '/', function() use ($userValidator) {
    if(!$userValidator->isLogin()) {
        header("Location: /logout");
        exit();
    }
});

$router->get('/login', function () use ($twig, $flash) {
    echo $twig->render('layout/login_layout.html', ['flash' => $flash]);
});

$router->get('/logout', function () use ($userAction) {
    require 'controller/logout.php';
});

$router->post('/do_login', function () use ($twig, $gump, $db, $userValidator, $userAction, $flash) {
    require 'controller/do_login.php';
});

// Run it!
$router->run();
