<?php

ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header("Access-Control-Allow-Headers: Accept, Origin, Content-Type, Authorization, X-Requested-With, x-client-key, x-client-token, x-client-secret");

define("ROOT", __DIR__);
require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../src/app.php';
//require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
$app->run();
