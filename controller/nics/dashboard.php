<?php
/**
 * Load page 
 */
echo $twig->render('pages/dashboard.html', ['menu_items' => Menu::ITEM_ARRAY, 'session' => $_SESSION, 'route' => $route]);
