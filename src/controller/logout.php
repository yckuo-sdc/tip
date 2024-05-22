<?php

$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$userAction->logger('logout', $_SERVER['REQUEST_URI']);
$duration = 3600 * 24 * 30; //3600sec*24hour*30day
setcookie('rememberme', '', time() - $duration, '/');
session_unset();  //It deletes only the variables from session and session still exists. Only data is truncated.
session_destroy();  //destroys all of the data associated with the current session

echo 'logout...';
header("Location: login");
