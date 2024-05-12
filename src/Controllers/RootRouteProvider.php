<?php
namespace Controllers;
use Symfony\Component\HttpFoundation\Request;
use Silet\Application;
use Silet\ControllerProviderInterface;

class RootRouteProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
    {
       
        $route = $app['controllers_factory'];
		$operatorRoleCheck = function (Request $request){
			//echo 'midleware';
		};
        $route->get('/', function () use ($app) {
            return $app->json([
                'status'     =>  200,
                'message'    => 'Hello world'
            ]);
			//$index = ROOT.'/root/index.html';
			//$index = ROOT.'/index.html';
			//return file_get_contents($index);
        })->before($operatorRoleCheck);;
        
        return $route;
    }
}
