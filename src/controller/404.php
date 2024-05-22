<?php

if(!$userValidator->isLogin()) {
    header("Location: /logout");
    return;
}
/**
 * 載入頁面
 */
echo $twig->render('header/default.html', ['menu_items' => Menu::ITEM_ARRAY, 'session' => $_SESSION]);
echo $twig->render('body/404.html');
echo $twig->render('footer/default.html', ['menu_items' => Menu::ITEM_ARRAY]);
