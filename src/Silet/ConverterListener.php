<?php
namespace Silet;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Handles converters.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConverterListener implements EventSubscriberInterface
{
    protected $routes;
    protected $callbackResolver;

    /**
     * Constructor.
     *
     * @param RouteCollection  $routes           A RouteCollection instance
     * @param CallbackResolver $callbackResolver A CallbackResolver instance
     */
    public function __construct(RouteCollection $routes, CallbackResolver $callbackResolver)
    {
        $this->routes = $routes;
        $this->callbackResolver = $callbackResolver;
    }

    /**
     * Handles converters.
     *
     * @param FilterControllerEvent $event The event to handle
     */
    //public function onKernelController(FilterControllerEvent $event)
     public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $route = $this->routes->get($request->attributes->get('_route'));
        if ($route && $converters = $route->getOption('_converters')) {
            foreach ($converters as $name => $callback) {
                $callback = $this->callbackResolver->resolveCallback($callback);

                $request->attributes->set($name, call_user_func($callback, $request->attributes->get($name), $request));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
