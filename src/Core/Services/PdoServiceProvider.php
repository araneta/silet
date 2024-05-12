<?php
namespace Core\Services;

use \PDO;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silet\Application;
use Silet\BootableProviderInterface;


class PdoServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
	
	public function boot(Application $app)
	{
		// do something
	}
	

    public function register(Container $app)
    {
		//return pdo instance
        $app['pdo.factory'] = $app->protect(
            function (               
            ) use ($app) {
				$dsn =  $app['pdo.dsn'];
				$user =  $app['pdo.user'];
				$password =  $app['pdo.password'];
				$pdo = new \PDO($dsn, $user, $password);
				$pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING );
				$pdo->exec('SET search_path TO '.$app['pdo.dbschema']);
                return $pdo;
            }
        );
		
        $app['pdo.defaults'] = array(
            'pdo.username' => null,
            'pdo.password' => null,
            'pdo.options' => array()
        );
    }
}
