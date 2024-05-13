<?php
namespace Corn\Services;

// use PDO;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silet\Application;
use Silet\BootableProviderInterface;
use Core\Mappers\PdoAdapter;
use Core\Services\MailerService;


class AppServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{

    public function register(Container $app)
    {
		//var_dump($app);exit();
        // init pdo adapter
        $app['pdo_adapter'] = new PdoAdapter($app);
        
        // register all services
		$app['mailerService'] = $app->protect(function () use ($app) {
            return new MailerService($app);
        });
        $app['userService'] = $app->protect(function () use ($app) {
            return new UserService($app);
        });        
        $app['moistureDataService'] = $app->protect(function () use ($app) {
            return new MoistureDataService($app);
        });        
        $app['translator'] =  new TranslatorService($app);
    }

    public function boot(Application $app)
    {
        // do something
        //var_dump($app);
    }
    
}
