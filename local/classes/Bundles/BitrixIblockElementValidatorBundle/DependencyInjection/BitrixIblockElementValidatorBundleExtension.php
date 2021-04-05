<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class BitrixIblockElementValidatorBundleExtension
 * @package Local\Bundles\BitrixIblockElementValidatorBundle\DependencyInjection
 *
 * @since 07.02.2021
 */
class BitrixIblockElementValidatorBundleExtension extends Extension
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

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );


        $container->setParameter('bitrix_iblock_element_validator.config', $config['iblocks']);
        $container->setParameter('bitrix_iblock_element_validator.enabled', $config['enabled']);

        if ($config['enabled']) {
            $loader->load('services.yaml');
        }
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'bitrix_iblock_element_validator';
    }
}
