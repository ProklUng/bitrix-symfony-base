<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Middleware;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;

class MiddlewareServiceFetcher
{
    private $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @param string[] $middlewares
     * @return MiddlewareInterface[]
     */
    public function fetchServices(array $middlewares): array
    {
        $result = [];

        foreach ($middlewares as $middleware_id) {
            $middleware = $this->container->get($middleware_id . MiddlewareEnum::ALIAS_SUFFIX);

            if (!$middleware instanceof MiddlewareInterface) {
                throw new \LogicException(
                    sprintf('Middleware [%s] must be instance of [%s]', $middleware_id, MiddlewareInterface::class)
                );
            }

            $result[] = $middleware;
        }

        return $result;
    }
}
