<?php

if(!$userValidator->isLogin()) {
    header("Location: /logout");
    return;
}
/**
 * Load page
 */
echo $twig->render('pages/404.html', ['menu_items' => Menu::ITEM_ARRAY, 'session' => $_SESSION, 'route' => $route]);
