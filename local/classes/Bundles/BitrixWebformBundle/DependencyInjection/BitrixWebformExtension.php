<?php

namespace Local\Bundles\BitrixWebformBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class BitrixWebformExtension
 * @package Local\Bundles\BitrixWebform\DependencyInjection
 *
 * @since 06.02.2021
 */
class BitrixWebformExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('validators.yaml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'bitrixwebform';
    }
}
