<?php

namespace Local\ServiceProvider\CompilePasses;

use Local\ServiceProvider\Examples\DummyService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ContainerAwareCompilerPass
 * @package Local\ServiceProvider\CompilePasses
 *
 * @since 28.09.2020
 */
class ContainerAwareCompilerPass implements CompilerPassInterface
{
    /**
     * automatically injects the Service Container into all your services that
     * implement Symfony\Component\DependencyInjection\ContainerAwareInterface.
     *
     * @see DummyService
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getServiceIds() as $serviceId) {
            $definition = $container->findDefinition($serviceId);
            $class = $definition->getClass();
            if (is_a($class, 'Symfony\Component\DependencyInjection\ContainerAwareInterface', true)
                &&
                !$definition->hasMethodCall('setContainer')) {
                $definition->addMethodCall('setContainer', array(new Reference('service_container')));
            }
        }
    }
}
