<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Route;

use Local\Bundles\SymfonyMiddlewareBundle\Middleware\MiddlewareServiceFetcher;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteMiddlewareResolver
 *
 * @package Local\Bundles\SymfonyMiddlewareBundle\Route
 */
class RouteMiddlewareResolver
{
    /**
     * @var RouteFetcher $routeFetcher
     */
    private $routeFetcher;

    /**
     * @var MiddlewareServiceFetcher $middlewareServiceFetcher
     */
    private $middlewareServiceFetcher;

    /**
     * RouteMiddlewareResolver constructor.
     *
     * @param RouteFetcher             $routeFetcher
     * @param MiddlewareServiceFetcher $middlewareServiceFetcher
     */
    public function __construct(
        RouteFetcher $routeFetcher,
        MiddlewareServiceFetcher $middlewareServiceFetcher
    ) {
        $this->routeFetcher = $routeFetcher;
        $this->middlewareServiceFetcher = $middlewareServiceFetcher;
    }

    /**
     * @param Router|RouteCollection $routeCollection Коллекция роутов.
     *
     * @return ResolvedRouteMiddleware[]
     */
    public function resolveMiddlewaresForCurrentRoute($routeCollection): array
    {
        $result = [];

        $routeWrapper = $this->routeFetcher->fetchCurrentRoute($routeCollection);

        if ($routeWrapper->getOriginalRoute() === null || $routeWrapper->getRouteName() === null) {
            return $result;
        }

        $middlewares = $routeWrapper->getOriginalRoute()->getOptions()['middleware'] ?? [];

        if (!empty($middlewares)) {
            $instances = $this->middlewareServiceFetcher->fetchServices($middlewares);

            foreach ($instances as $instance) {
                $result[] = new ResolvedRouteMiddleware($routeWrapper->getRouteName(), $instance);
            }
        }

        return $result;
    }
}
