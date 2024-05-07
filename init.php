<?php

date_default_timezone_set('Asia/Taipei');

$route = new Router(Request::uri());  //搭配 .htaccess 排除資料夾名稱後解析 URL

$template = Tamtamchik\SimpleFlash\TemplateFactory::create(Tamtamchik\SimpleFlash\Templates::SEMANTIC);  // get template from factory, e.g. template for Foundation

$flash = new Tamtamchik\SimpleFlash\Flash($template);  // passing to constructor

$gump = new GUMP();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);  //Loads environment variables
$dotenv->load();

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ .'/templates');
$twig = new \Twig\Environment($loader);


$db = Database::get();

$userValidator = new UserValidator();
$userAction = new UserAction();
