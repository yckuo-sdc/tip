<?php
/**
 * Load page 
 */
echo $twig->render('pages/search.html', ['menu_items' => Menu::ITEM_ARRAY, 'session' => $_SESSION, 'route' => $route]);
