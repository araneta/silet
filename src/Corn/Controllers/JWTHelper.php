<?php

namespace Corn\Controllers;

use Silet\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Configs\GlobalConfig;

class JWTHelper {

    

    /**
     * check user roles
     * @param String $roles the requested roles
     * return Response
     */
    public static function validate(Application $app, $roles = '') {

        $before = function (Request $request) use ($app, $roles) {

            // Strip out the bearer
            $rawHeader = $request->headers->get('Authorization');
            if (strpos($rawHeader, 'Bearer ') === false) {
                return new Response('Bad Authorization Header: ' . $rawHeader, 401);
            }

            $headerWithoutBearer = str_replace('Bearer ', '', $rawHeader);
            if (empty($headerWithoutBearer)) {
                return new Response('Bad Authorization Header: ' . $rawHeader, 401);
            }
            try {
                $key = GlobalConfig::getJWTKey();                
                $decodedJWT = JWT::decode($headerWithoutBearer,  new Key($key, 'HS256'));
            } catch (\Exception $e) {
                return new Response('Invalid Token', 401);
            }

            $app['payload'] = $decodedJWT->payload;
            if (!empty($roles)) {
                $userroles = $app['payload']->roles;

                //if($roles!=$userroles){
                if (is_string($roles)) {
                    if (strpos($userroles, $roles) === FALSE) {
                        return new Response('You don\'t have access.  ', 401);
                    }
                } else if (is_array($roles)) {
                    $denyAll = TRUE;
                    foreach ($roles as $allowedRole) {
                        if (strpos($userroles, $allowedRole) === FALSE) {
                            
                        } else {
                            $denyAll = FALSE;
                            break;
                        }
                    }
                    if ($denyAll) {
                        return new Response('You don\'t have access.: ', 401);
                    }
                }
            }
        };
        return $before;
    }

    public static function getWebToken($data, $isAdmin = false) {
        $time = 60 * 60 * 1;
        if ($isAdmin) {
            $time = 60 * 60 * 3;
        }
        $jsonObject = array(
            // Registered Claims
            //"jti" => base64_encode(mcrypt_create_iv(32)),
            "jti" => base64_encode(openssl_random_pseudo_bytes(32)),
            "iss" => "University", // Claiming Issure
            "aud" => "http://university", // Intended Audience
            "iat" => time(), // Issued At Time
            //"nbf" => time()+ 30, // Not Before Time
            "exp" => time() + $time, // Expiration Time (24 hours)
            // Public Claims
            "payload" => $data
        );

        // Sign the JWT with the secret key
        $key = GlobalConfig::getJWTKey();
        
        $jsonWebToken = JWT::encode($jsonObject, $key, 'HS256');
        return $jsonWebToken;
    }

    
}
