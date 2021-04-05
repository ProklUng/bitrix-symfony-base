<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Middleware;

use Local\Bundles\SymfonyMiddlewareBundle\GlobalMiddlewareInterface;

class GlobalMiddlewareMapper
{
    /**
     * @param GlobalMiddlewareWrapper[] $globalMiddlewares
     * @return GlobalMiddlewareInterface[]
     */
    public function fromWrapper(array $globalMiddlewares): array
    {
        return array_map(static function (GlobalMiddlewareWrapper $middleware) {
            return $middleware->getMiddleware();
        }, $globalMiddlewares);
    }
}
