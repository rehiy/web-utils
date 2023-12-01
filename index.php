<?php

define('APP_ROOT', __DIR__ . '/');

include 'include/helper.php';
include 'include/output.php';

if (empty($_GET['act'])) {
    $uris = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    $_GET['act'] = $_REQUEST['act'] = array_shift($uris) ?: 'index';
}

include 'include/pages/api.php';

include 'include/pages/act.php';
page_act();
