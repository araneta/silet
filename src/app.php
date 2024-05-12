<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Silet\Application;
use Silet\CorsServiceProvider;
use Configs\GlobalConfig;
use Core\Services\PdoServiceProvider;
use Corn\Services\AppServiceProvider;

$app = new Application();
//DB
$app->register(
    new PdoServiceProvider(),
    array(
		'pdo.dsn' => 'pgsql:dbname='.GlobalConfig::getDBName().';host='.GlobalConfig::getDBHost(). ';port=' . GlobalConfig::getDBPort().';',
		'pdo.user' => GlobalConfig::getDBUser(),
		'pdo.password' => GlobalConfig::getDBPassword(),
       'pdo.dbschema' => GlobalConfig::getDBSchema(),
    )
);

//CORS
//$app->register(new CorsServiceProvider(), array(
  //  "cors.allowOrigin" => "*",
//));
//$app->after($app["cors"]);

$app->register(new AppServiceProvider());

//accepting JSON
$app->before(function (Request $request) {

    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});
return $app;
