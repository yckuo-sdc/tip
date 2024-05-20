<?php

if (isset($_GET['sid'])) {
    session_id($_GET['sid']);
}	# fetch and update Session ID with sso_vision.php
session_start();

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/init.php';
require __DIR__ . '/route.php';
# require __DIR__ . '/bramus_router.php';

