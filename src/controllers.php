<?php
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
\Sentry\init([
  'dsn' => 'https://29071abb9cdf417f9e28cabe43968794@o89294.ingest.us.sentry.io/211093',
  // Specify a fixed sample rate
  'traces_sample_rate' => 1.0,
  // Set a sampling rate for profiling - this is relative to traces_sample_rate
  'profiles_sample_rate' => 1.0,
]);

$app->mount('/', new Controllers\RootRouteProvider);
$app->get('/info', function () use ($app) {
	//phpinfo();

    return 'Silet. By AldoApp http://www.aldoapp.com/';
});

$app->mount('/api', new Corn\Controllers\APIRouteProvider);

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
	\Sentry\captureException($e);
    
   

    //return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
    if($e instanceof ServiceException){
		$code = 200;
	}
    //FALLBACK
    /*
    $path = $request->getPathInfo();
    $segments = explode('/', $path);
    if($segments!= NULL && count($segments)>2){
        $root = $segments[1];
        $sites = ['admin', 'student', 'staff', 'academic', 'academic-admin' , 'report'];
        if(in_array($root, $sites)){
            if ($e instanceof NotFoundHttpException) {
                //var_dump($segments);
                $index = ROOT.'/'.$root.'/index.html';
                //echo $index;
                if(file_exists($index)){
                    return file_get_contents($index);    
                }
                
            }    
        }            
    }*/
    //echo $path; ex:/api/path1/path2
    //exit(0);
    $message = $e->getMessage();
    //DEBUG MODE
    if($message!="Expired token"){
	//$message = sprintf('Message: %s<br />Stack Trace: %s',$e->getMessage(), $e->getTraceAsString());
	$message = sprintf('%s',$e->getMessage());
    }
    
    if($e instanceof NotFoundHttpException || $e instanceof ExpiredException){
		//$client = $app['loggerService']();
		//$client->captureException($e);
    }else{
		//$client = $app['loggerService']();
		//$client->captureException($e);
    }
    
    return new JsonResponse(['title'=>'error', 'error'=>['title'=>'Exception', 'message'=>$message]], $code);
    
});
