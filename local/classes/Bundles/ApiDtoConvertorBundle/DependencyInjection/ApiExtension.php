<?php

declare(strict_types=1);

namespace Local\Bundles\ApiDtoConvertorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Local\ServiceProvider\Utils\IgnoredAutowiringControllerParamsBag;

class ApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('service.xml');


        $processedConfigs = $this->processConfiguration(new Configuration(), $configs);
    }
}
