<?php

namespace Local\Bundles\SymfonyBladeBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class SymfonyBladeExtension
 * @package Local\Bundles\SymfonyBlade\DependencyInjection
 *
 * @since 08.03.2021
 */
class SymfonyBladeExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('symfony_blade.base_path', $config['baseViewPath']);
        $container->setParameter('symfony_blade.base_view_path', $config['baseViewPath']);
        $container->setParameter('symfony_blade.cache_path', $config['cachePath']);
        $container->setParameter('symfony_readonly', $config['readonly']);

        $container->setParameter('symfony_blade', $config);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('directives.yaml');
        $loader->load('filters.yaml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'symfony_blade';
    }
}
