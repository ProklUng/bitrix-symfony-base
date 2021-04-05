<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\ServiceLocator;

use Local\Bundles\SymfonyMiddlewareBundle\GlobalMiddlewareInterface;
use Local\Bundles\SymfonyMiddlewareBundle\Middleware\GlobalMiddlewareWrapper;
use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;
use Local\Bundles\SymfonyMiddlewareBundle\Route\RouteMiddlewareResolver;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class MiddlewareServiceLocator
 *
 * @package Local\Bundles\SymfonyMiddlewareBundle\ServiceLocator
 */
class MiddlewareServiceLocator
{
    private $globalMiddleware = [];
    private $controllerMiddleware = [];
    private $controllerActionMiddleware = [];
    private $routeMiddleware = [];
    private $routeMiddlewareResolver;

    /**
     * MiddlewareServiceLocator constructor.
     *
     * @param RouteMiddlewareResolver $routeMiddlewareResolver
     */
    public function __construct(
        RouteMiddlewareResolver $routeMiddlewareResolver
    ) {
        $this->routeMiddlewareResolver = $routeMiddlewareResolver;
    }

    /**
     * @param GlobalMiddlewareInterface $middleware
     * @param integer $priority
     */
    public function addGlobalMiddleware(GlobalMiddlewareInterface $middleware, int $priority = 0): void
    {
        $this->globalMiddleware[] = new GlobalMiddlewareWrapper($middleware, $priority);
    }

    /**
     * @return GlobalMiddlewareWrapper[]
     */
    public function getGlobalMiddlewares(): array
    {
        return $this->globalMiddleware;
    }

    /**
     * @param string $controller_fqcn
     * @param MiddlewareInterface $middleware
     */
    public function addControllerMiddleware(string $controller_fqcn, MiddlewareInterface $middleware): void
    {
        $this->controllerMiddleware[$controller_fqcn][] = $middleware;
    }

    /**
     * @param string $controller_fqcn
     *
     * @return MiddlewareInterface[]
     */
    public function getControllerMiddlewares(string $controller_fqcn): array
    {
        return $this->controllerMiddleware[$controller_fqcn] ?? [];
    }

    /**
     * @param string $controller_fqcn
     * @param string $action
     * @param MiddlewareInterface $middleware
     *
     * @return void
     */
    public function addControllerActionMiddleware(
        string $controller_fqcn,
        string $action,
        MiddlewareInterface $middleware
    ): void {
        $this->controllerActionMiddleware[$controller_fqcn][$action][] = $middleware;
    }

    /**
     * @param string $controller_fqcn
     * @param string $action
     *
     * @return MiddlewareInterface[]
     */
    public function getControllerActionMiddlewares(string $controller_fqcn, string $action): array
    {
        return $this->controllerActionMiddleware[$controller_fqcn][$action] ?? [];
    }

    /**
     * @param Router|RouteCollection $routeCollection
     *
     * @return void
     */
    public function addRouteMiddlewares($routeCollection): void
    {
        if ($routeCollection instanceof Router) {
            $routeCollection = $routeCollection->getRouteCollection();
        }

        $resolved_route_middlewares = $this->routeMiddlewareResolver->resolveMiddlewaresForCurrentRoute(
            $routeCollection
        );

        foreach ($resolved_route_middlewares as $resolved_route_middleware) {
            $routeName = $resolved_route_middleware->getRouteName();
            $this->routeMiddleware[$routeName][] = $resolved_route_middleware->getMiddleware();
        }
    }

    /**
     * @param string $route_name
     *
     * @return MiddlewareInterface[]
     */
    public function getRouteMiddlewares(string $route_name): array
    {
        return $this->routeMiddleware[$route_name] ?? [];
    }
}
