<?php
namespace Silet;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;


/**
 * Manages the route middlewares.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MiddlewareListener implements EventSubscriberInterface
{
    protected $app;

    /**
     * Constructor.
     *
     * @param Application $app An Application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Runs before filters.
     *
     * @param GetResponseEvent $event The event to handle
     */
    //public function onKernelRequest(GetResponseEvent $event)
    public function onKernelRequest(RequestEvent $event)    
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if (!$route = $this->app['routes']->get($routeName)) {
            return;
        }

        foreach ((array) $route->getOption('_before_middlewares') as $callback) {
            $ret = call_user_func($this->app['callback_resolver']->resolveCallback($callback), $request, $this->app);
            if ($ret instanceof Response) {
                $event->setResponse($ret);

                return;
            } elseif (null !== $ret) {
                throw new \RuntimeException(sprintf('A before middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }

    /**
     * Runs after filters.
     *
     * @param FilterResponseEvent $event The event to handle
     */
    //public function onKernelResponse(FilterResponseEvent $event)
    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if(!$routeName){
			return;
		}
        if (!$route = $this->app['routes']->get($routeName)) {
            return;
        }

        foreach ((array) $route->getOption('_after_middlewares') as $callback) {
            $response = call_user_func($this->app['callback_resolver']->resolveCallback($callback), $request, $event->getResponse(), $this->app);
            if ($response instanceof Response) {
                $event->setResponse($response);
            } elseif (null !== $response) {
                throw new \RuntimeException(sprintf('An after middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // this must be executed after the late events defined with before() (and their priority is -512)
            KernelEvents::REQUEST => ['onKernelRequest', -1024],
            KernelEvents::RESPONSE => ['onKernelResponse', 128],
        ];
    }
}
