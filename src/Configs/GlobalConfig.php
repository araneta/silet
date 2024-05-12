<?php

namespace Configs;

class GlobalConfig
{
    const LOGO_MAX_WIDTH = 240;
    const LOGO_MAX_HEIGHT = 240;
	const SECRET_KEY = 'I-/^-{?Q0yZ|>tY6;tbEV]/fGJBka8UClh4PaEA :w,TFY)s+=/G(+):2KAUH]V!';
    private static $dbHost = 'localhost';
	private static $dbPort = '5432';
	private static $dbUser = '';//'university';
	private static $dbPass = '(g){Pjb);CCR';//'willamette';
	private static $dbName = '';//'university';
	private static $dbSchema='cornmoisture';
	
	private static $publicURL = "http://localhost:8000";
    private static $amqpHost,$amqpPort, $amqpUser, $amqpPass;


    public static function init(){
        

    }

    public static function getDBHost()
    {
        return self::$dbHost;
    }
	public static function getDBSchema()
    {
        return self::$dbSchema;
    }
    public static function getDBPort()
    {
       return self::$dbPort;
    }

    public static function getDBUser()
    {
        return self::$dbUser;
    }

    public static function getDBPassword()
    {
       return self::$dbPass;
    }

    public static function getDBName()
    {
        return self::$dbName;
    }

    /**
     * always trailing slash
     **/
    public static function getImagePath()
    {
        $path = getenv('OPENSHIFT_DATA_DIR');
        if (empty($path)) {
            $path = __DIR__ . '/../../../web/upload/';
        }
        return $path;
    }
	
    public static function getPublicURL(){
		return self::$publicURL;
    }
    
    public static function getMailerConfig(){	
		return array(
			"host"=>"smtp-relay.sendinblue.com",
			"username" => "aldopraherda@gmail.com",
			"password" => "bxKLX3GYmr6gj72D",
			"smtpauth" => true,
			"smtpsecure" => "tls",
			"port"=>587
		);
    }
    
    public static function getTimeZone() {
		return 'Asia/Jakarta';
	}
	
	public static function getJWTKey(){
		return self::SECRET_KEY;
	}
	
	public static function getPublicDir() {
		return '/media/aldo/49909430-d2bd-4bcf-be1d-3c425a4013bf/apps/projects/corn-moisture-detector/apps/simp/web/assets';
	}
    
}
