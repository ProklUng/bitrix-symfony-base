<?php

namespace Local\Bundles\RequestValidatorBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
  */
class RequestValidatorExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['enabled'] === false) {
            return;
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if ($config['cache_annotations'] === true) {
            try {
                $annotationCachedDefinition = $container->findDefinition('annotations.cached_reader');

                $readerDefinition = $container->findDefinition('request_validator_bundle.validator_annotation_listener');
                $readerDefinition->replaceArgument(0, $annotationCachedDefinition);
            } catch (ServiceNotFoundException $e) {
                return;
            }
        }
    }
}
