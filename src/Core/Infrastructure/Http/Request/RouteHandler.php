<?php

declare(strict_types=1);

namespace Phractico\Core\Infrastructure\Http\Request;

use Phractico\Core\Infrastructure\Http\Controller;
use Phractico\Core\Infrastructure\Http\Response;
use Phractico\Core\Infrastructure\DI\ContainerRegistry;
use Phractico\Core\Infrastructure\Http\Response\Factory\JsonResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouteHandler
{
    private static array $stack;

    public static function init(array $controllers): void
    {
        self::$stack = [];
        foreach ($controllers as $instance) {
            if (!isset(self::$stack[$instance])) {
                /** @var Controller $controller */
                $container = ContainerRegistry::getContainer();
                $controller = $container->get($instance);
                $routes = $controller->routes();
                self::$stack[$instance] = $routes->getRoutesMapping();
            }
        }
    }

    public static function handle(
        RequestInterface $request
    ): ResponseInterface {
        $httpMethod = $request->getMethod();
        $resource = $request->getRequestTarget();
        $route = Route::create($httpMethod, $resource);

        $container = ContainerRegistry::getContainer();

        foreach (self::$stack as $controllerInstance => $controllerRouteMapping) {
            /** @var Route $controllerRoute */
            foreach ($controllerRouteMapping as $controllerAction => $controllerRoute) {
                if ($controllerRoute->match($route)) {
                    $controller = $container->get($controllerInstance);
                    /** @var Response $controllerResponse */
                    $controllerResponse = $controller->$controllerAction();
                    try {
                        return $controllerResponse->render();
                    } catch (\Throwable $e) {
                        return JsonResponseFactory::badRequestError($e->getMessage())->render();
                    }
                }
            }
        }

        $internalServerErrorResponse = JsonResponseFactory::internalServerError();
        return $internalServerErrorResponse->render();
    }
}
