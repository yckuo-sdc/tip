<?php

/**
 * 載入頁面
 */
echo $twig->render('header/default.html', ['menu_items' => Menu::ITEM_ARRAY, 'session' => $_SESSION]);
echo $twig->render('body/nics/search.html', ['route' => $route]);
echo $twig->render('footer/default.html', ['menu_items' => Menu::ITEM_ARRAY]);


