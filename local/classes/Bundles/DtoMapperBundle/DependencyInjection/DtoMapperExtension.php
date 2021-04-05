<?php

namespace Local\Bundles\DtoMapperBundle\DependencyInjection;

use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperPlusBundle;
use Exception;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class DtoMapperExtension
 * @package Local\Bundles\DtoMapper\DependencyInjection
 *
 * @since 26.02.2021
 */
class DtoMapperExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $this->checkDependencies();

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('property_extractor.yaml');
        $loader->load('services.yaml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'dtomapper';
    }

    /**
     * Проверка наличия AutoMapperPlus и соответствующего бандла.
     *
     * @return void
     */
    private function checkDependencies() : void
    {
        if (!class_exists(AutoMapper::class)) {
            throw new RuntimeException(
                'Class AutoMapper not exists. Forget install? Try run composer require mark-gerarts/auto-mapper-plus.'
            );
        }

        if (!class_exists(AutoMapperPlusBundle::class)) {
            throw new RuntimeException(
                'Class AutoMapperPlusBundle not exists. Forget install and config AutoMapperPlusBundle?
                 Try run composer require mark-gerarts/automapper-plus-bundle.'
            );
        }
    }
}
