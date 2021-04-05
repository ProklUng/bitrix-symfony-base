<?php

declare(strict_types=1);

namespace Local\Bundles\CustomRequestResponserBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class CustomRequestResponserExtension
 * @package Local\Bundles\CustomRequestResponserBundle\DependencyInjection
 *
 * @since 04.12.2020
 */
class CustomRequestResponserExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function getAlias()
    {
        return 'custom_request_responser';
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

        $container->setParameter('custom_request_responser', $config);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('listeners.yaml');
    }
}
