<?php
namespace Corn\Controllers;
use Symfony\Component\HttpFoundation\Request;
use Silet\Application;
use Silet\ControllerProviderInterface;

class APIRouteProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
    {
       
        $route = $app['controllers_factory'];
		
		$roleCheck = JWTHelper::validate($app, '');
		
        $route->get('/user', function () use ($app) {
            return $app->json([
                'status'     =>  200,
                'message'    => 'Hello world'
            ]);			
        });
        
        $route->post('/signup', function (Application $app, Request $request) {
			return  UserController::register($app, $request);            
        });
        $route->post('/login', function (Application $app, Request $request) {
            return UserController::login($app, $request);
        });
        
        $route->post('/send-new-password', function (Application $app, Request $request) {
            return UserController::sendNewPassword($app, $request);
        });
        $route->post('/update-profile', function (Application $app, Request $request) {
			return  UserController::updateProfile($app, $request);            
        });
        
        $route->get('/user/activate/{userID}/{activationCode}', function (Application $app, Request $request, $userID, $activationCode) {
			return  UserController::activateUser($app, $request,$userID, $activationCode);            
        });
        
        $route->post('/moisture-data', function (Application $app, Request $request) {
			return  MoistureDataController::save($app, $request);            
        })->before($roleCheck);
        
        $route->get('/moisture-data', function (Application $app, Request $request) {
			return  MoistureDataController::search($app, $request);            
        })->before($roleCheck);
        
        return $route;
    }
}
