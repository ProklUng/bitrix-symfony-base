<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Route;

use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;

/**
 * Class ResolvedRouteMiddleware
 *
 * @package Local\Bundles\SymfonyMiddlewareBundle\Route
 */
final class ResolvedRouteMiddleware
{
    /**
     * @var string $routeName
     */
    private $routeName;

    /**
     * @var MiddlewareInterface $middleware
     */
    private $middleware;

    /**
     * ResolvedRouteMiddleware constructor.
     *
     * @param string              $routeName
     * @param MiddlewareInterface $middleware
     */
    public function __construct(string $routeName, MiddlewareInterface $middleware)
    {
        $this->routeName = $routeName;
        $this->middleware = $middleware;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return MiddlewareInterface
     */
    public function getMiddleware(): MiddlewareInterface
    {
        return $this->middleware;
    }
}
