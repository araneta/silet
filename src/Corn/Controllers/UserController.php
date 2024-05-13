<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Corn\Controllers;

use Silet\Application;
use Symfony\Component\HttpFoundation\Request;
use Corn\Entities\SendNewPasswordForm;
use Corn\Entities\RegisterUserForm;
use Corn\Entities\UpdateProfileForm;
/**
 * Description of UserController
 *
 * @author aldo
 */
class UserController
{
	public static function getAllUsers(Application $app, Request $request)
	{
		
		$userService = $app['userService']();
		try{
			return $app->json([
				'status' => 1,
				'message' => $userService->findAll(),
			]);
		}catch(\Exception $ex){
			return $app->json([
				'status' => 0,
				'message' => $userService->transx('Exception').' '.$ex->getMessage(),
			]);
			
		}
	}
	
    public static function verify(Application $app, Request $request)
    {
        $payload = $app['payload'];

        return $app->json([
            'status' => 1,
            'message' => $payload,
        ]);
    }

    
    public static function register(Application $app, Request $request)
    {
        $form = new RegisterUserForm();
        $form->bindRequest($request);
        // var_dump($form);
        $userService = $app['userService']();
        $ret = $userService->registerUser($form);
        if ($ret == FALSE) {
            return $app->json([
                'status' => 0,
                'message' => 'Failed to create user'
            ]);
        } else {
            return $app->json([
                'status' => 1,
                'message' => $ret
            ]);
        }
    }
    
    
    public static function activateUser(Application $app, Request $request,$userID, $activationCode)
    {
        $userService = $app['userService']();
        $ret = $userService->activateUser($userID, $activationCode);
        if ($ret == FALSE) {
            return $app->json([
                'status' => 0,
                'message' => 'Failed to create user'
            ]);
        } else {
            return $app->json([
                'status' => 1,
                'message' => $ret
            ]);
        }
    }

    public static function login(Application $app, Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        $userService = $app['userService']();

        $user = $userService->login($username, $password);

        if ($user) {
			
			$data = [
				'userId' => $user->ID,
			];
			$jsonWebToken = JWTHelper::getWebToken($data, TRUE);
			
			return $app->json([
				'status' => 1,
				'message'=>[
					'ID'=>$user->ID,
					'token' => $jsonWebToken,			
					'firstName'=>$user->firstName,
					'lastName'=>$user->lastName,
					'email'=>$user->email,
					'mobileNo'=>$user->mobileNo,
					'avatarFile'=>$user->avatarFile,
					//'UserID' => $user->ID,
					'avatarURL' => $user->avatarURL,				
				]
			]);

        }
        return $app->json([
            'status' => 0,
            'message' => 'Failed to Authenticate'
        ]);
    }

    public static function sendNewPassword(Application $app, Request $request)
    {
        $form = new SendNewPasswordForm();
        $form->bindRequest($request);
        // var_dump($form);

        if (!$form->validate()) {
            return $app->json([
                'status' => 0,
                'message' => $form->error_messages()
            ]);
        }
        // reset
        $svc = $app['userService']();
        $ret = $svc->sendNewPassword($form);
        if ($ret) {
            return $app->json([
                'status' => 1,
                'message' => 'Please check your email'
            ]);
        }
        return $app->json([
            'status' => 0,
            'message' => 'Failed to reset password'
        ]);
    }

    public static function updateProfile(Application $app, Request $request)
    {
        $form = new UpdateProfileForm();
        $form->bindRequest($request);
        // var_dump($form);
        $userService = $app['userService']();
        $ret = $userService->updateProfile($form);
        if ($ret == FALSE) {
            return $app->json([
                'status' => 0,
                'message' => 'Failed to update user'
            ]);
        } else {
            return $app->json([
                'status' => 1,
                'message' => $ret
            ]);
        }
    }
}
