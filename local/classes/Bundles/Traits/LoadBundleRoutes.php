<?php

namespace Local\Bundles\Traits;

use InvalidArgumentException;
use Local\SymfonyTools\Router\RouterConfig;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * Trait LoadBundleRoutes
 * @package Local\Bundles\Traits
 *
 * @since 08.11.2020
 */
trait LoadBundleRoutes
{
    /**
     * Загрузить роуты в бандле.
     *
     * @param string $path   Путь к конфигу.
     * @param string $config Конфигурационный файл.
     *
     * @return void
     *
     * @throws InvalidArgumentException Нет класса-конфигуратора роутов.
     */
    protected function loadRoutes(string $path, string $config = 'routes.yaml') : void
    {
        $routeLoader = new YamlFileLoader(
            new FileLocator($path)
        );

        $routes = $routeLoader->load($config);

        if (class_exists(RouterConfig::class)) {
            RouterConfig::addRoutesBundle($routes);
            return;
        }

        throw new InvalidArgumentException(
            'Router config class Local\SymfonyTools\Router\RouterConfig not exist.'
        );
    }
}
