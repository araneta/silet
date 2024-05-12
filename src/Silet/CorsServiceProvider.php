<?php

namespace Silet;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
class Allow
{
    public function __invoke(Request $request, Response $response, Application $app)
    {
        $requestMethod = $app["request_context"]->getMethod();
        $app["request_context"]->setMethod("NOTAMETHOD");

        try {
			echo "trelo";
            $app["request_matcher"]->match($request->getPathInfo());
            echo "trelo2";
            $allow = []; // Should never get here
        } catch (MethodNotAllowedException $e) {
			echo "trelo33";
            $allow = array_filter($e->getAllowedMethods(), function ($method) {
                return $method != "OPTIONS";
            });
            echo "trelo566";
        } catch (ResourceNotFoundException $e) {
            $allow = []; // Should never get here
            echo "trelo888";
        }

        $app["request_context"]->setMethod($requestMethod);

        if (count($allow) === 0) {
            throw new NotFoundHttpException();
        }

        $response->headers->set("Allow", implode(",", $allow));

        return $response;
    }
}

/**
 * The CORS service provider provides a `cors` service that a can be included in your project as application middleware.
 */
class CorsServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * Add OPTIONS method support for all routes
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $app->on(KernelEvents::EXCEPTION, function (ExceptionEvent $event) {
            $e = $event->getThrowable();
            if ($e instanceof MethodNotAllowedHttpException && $e->getHeaders()["Allow"] === "OPTIONS") {
                $event->setThrowable(new NotFoundHttpException("No route found for \"{$event->getRequest()->getMethod()} {$event->getRequest()->getPathInfo()}\""));
            }
        });
    }

    /**
     * Register the cors function and set defaults
     *
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app["cors.allowOrigin"] = "*"; // Defaults to all
        $app["cors.allowMethods"] = null; // Defaults to all
        $app["cors.allowHeaders"] = null; // Defaults to all
        $app["cors.maxAge"] = null;
        $app["cors.allowCredentials"] = null;
        $app["cors.exposeHeaders"] = null;

        $app["allow"] = $app->protect(new Allow());

        $app["options"] = $app->protect(function ($subject) use ($app) {
            $optionsController = function () {
                return Response::create("", 204);
            };

            if ($subject instanceof Controller) {
                $optionsRoute = $app->options($subject->getRoute()->getPath(), $optionsController)
                    ->after($app["allow"]);
            } else {
                $optionsRoute = $subject->options("{path}", $optionsController)
                    ->after($app["allow"])
                    ->assert("path", ".*");
            }

            return $optionsRoute;
        });

        $app["cors-enabled"] = $app->protect(function ($subject, $config = []) use ($app) {
            $optionsController = $app["options"]($subject);
            $cors = new Cors($config);

            if ($subject instanceof Controller) {
                $optionsController->after($cors);
            }

            $subject->after($cors);

            return $subject;
        });

        $app["cors"] = function () use ($app) {
            $app["options"]($app);
            return new Cors();
        };
    }
}
