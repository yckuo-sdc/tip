<?php
// Create Router instance
$router = new \Bramus\Router\Router();

$router->get('/', function() {
    echo 'About Page Contents';
});
// Define routes
// ...

// Run it!
$router->run();
