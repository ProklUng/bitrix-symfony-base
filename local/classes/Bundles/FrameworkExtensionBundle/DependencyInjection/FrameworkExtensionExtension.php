<?php

namespace Local\Bundles\FrameworkExtensionBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class FrameworkExtensionExtension
 * @package Local\Bundles\FrameworkExtension\DependencyInjection
 *
 * @since 13.04.2021
 */
class FrameworkExtensionExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('listeners.yaml');
        $loader->load('bitrix.yaml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'framework_extension';
    }
}
