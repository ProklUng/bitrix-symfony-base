<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Local\Bundles\SymfonyMiddlewareBundle\Controller\ControllerMetadata;
use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;
use Local\Bundles\SymfonyMiddlewareBundle\ServiceLocator\MiddlewareServiceLocator;

class MiddlewareFacade
{
    private $middlewareServiceLocator;
    private $middlewareMerger;
    private $globalMiddlewareMapper;
    private $globalMiddlewareWrapperSorter;

    public function __construct(
        MiddlewareServiceLocator $middlewareServiceLocator,
        MiddlewareMerger $middlewareMerger,
        GlobalMiddlewareMapper $globalMiddlewareMapper,
        GlobalMiddlewareWrapperSorter $globalMiddlewareWrapperSorter
    ) {
        $this->middlewareServiceLocator = $middlewareServiceLocator;
        $this->middlewareMerger = $middlewareMerger;
        $this->globalMiddlewareMapper = $globalMiddlewareMapper;
        $this->globalMiddlewareWrapperSorter = $globalMiddlewareWrapperSorter;
    }

    /**
     * @param ControllerMetadata $controllerMetadata
     * @param Request $request
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewaresToHandle(ControllerMetadata $controllerMetadata, Request $request): array
    {
        $globalMiddlewares = $this->middlewareServiceLocator->getGlobalMiddlewares();

        $globalMiddlewares = $this->globalMiddlewareWrapperSorter->sortDescByPriority($globalMiddlewares);
        $globalMiddlewares = $this->globalMiddlewareMapper->fromWrapper($globalMiddlewares);

        $controllerActionMiddlewares = $this->middlewareServiceLocator->getControllerActionMiddlewares(
            $controllerMetadata->getControllerFqcn(),
            $controllerMetadata->getControllerAction()
        );

        $controllerMiddlewares = $this->middlewareServiceLocator->getControllerMiddlewares(
            $controllerMetadata->getControllerFqcn()
        );

        $routeMiddlewares = $this->middlewareServiceLocator->getRouteMiddlewares($request->get('_route', ''));

        return $this->middlewareMerger->merge(
            $globalMiddlewares,
            $controllerMiddlewares,
            $controllerActionMiddlewares,
            $routeMiddlewares
        );
    }
}
