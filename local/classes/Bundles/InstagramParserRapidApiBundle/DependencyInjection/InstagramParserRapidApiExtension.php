<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class InstagramParserRapidApiExtension
 * @package Local\Bundles\InstagramParserRapidApi\DependencyInjection
 *
 * @since 22.02.2021
 */
class InstagramParserRapidApiExtension extends Extension
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

        if (!$config['defaults']['enabled']) {
            return;
        }

        $container->setParameter('instagram_parser_rapid_api.instagram_user_id', $config['instagram_user_id']);
        $container->setParameter('instagram_parser_rapid_api.instagram_user_name', $config['instagram_user_name']);
        $container->setParameter('instagram_parser_rapid_api.rapid_api_key', $config['rapid_api_key']);
        $container->setParameter('instagram_parser_rapid_api.cache_ttl', $config['cache_ttl']);
        $container->setParameter('instagram_parser_rapid_api.cache_user_data_ttl', $config['cache_user_data_ttl']);
        $container->setParameter('instagram_parser_rapid_api.cache_path', $config['cache_path']);
        $container->setParameter('instagram_parser_rapid_api.mock', $config['mock']);
        $container->setParameter('instagram_parser_rapid_api.fixture_response_path', $config['fixture_response_path']);
        $container->setParameter('instagram_parser_rapid_api.fixture_user_path', $config['fixture_user_path']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'instagram_parser_rapid_api';
    }
}
