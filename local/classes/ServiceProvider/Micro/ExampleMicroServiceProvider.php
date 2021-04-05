<?php

namespace Local\ServiceProvider\Micro;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ExampleMicroServiceProvider
 * @package Local\ServiceProvider\Micro
 *
 * Пример микро-сервиспровайдера (для модулей и т.п.)
 *
 * @since 04.03.2021
 */
class ExampleMicroServiceProvider extends AbstractStandaloneServiceProvider
{
    /**
     * @var ContainerBuilder $containerBuilder Контейнер.
     */
    protected static $containerBuilder;

    /**
     * @var string $pathBundlesConfig Путь к конфигурации бандлов.
     */
    protected $pathBundlesConfig = '/local/modules/example.module/lib/standalone_bundles.php';

    /**
     * @var string $configDir Папка, где лежат конфиги.
     */
    protected $configDir = '/local/modules/example.module/config/';
}
