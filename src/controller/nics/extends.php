<?php
/**
 * Load page 
 */
echo $twig->render('pages/child.html', ['menu_items' => Menu::ITEM_ARRAY, 'session' => $_SESSION, 'route' => $route]);
