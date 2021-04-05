<?php

declare(strict_types=1);

namespace Local\Bundles\CustomArgumentResolverBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class CustomArgumentResolver
 * @package Local\Bundles\CustomArgumentResolver\DependencyInjection
 *
 * @since 04.12.2020
 */
class CustomArgumentResolverBundleExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function getAlias() : string
    {
        return 'custom_arguments_resolvers';
    }

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['defaults']['enabled']) {
            return;
        }

        $container->setParameter('custom_arguments_resolvers', $config);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');

        if ($container->getParameter('kernel.environment') === 'dev') {
            $loader->load('dev/services.yaml');
        }

        $loader->load('listeners.yaml');
        $loader->load('resolvers.yaml');
    }
}
