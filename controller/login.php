<?php
/**
 * 載入頁面
 */
echo $twig->render('header/login.html');
echo $twig->render('body/login.html', ['flash' => $flash]);
echo $twig->render('footer/login.html');

