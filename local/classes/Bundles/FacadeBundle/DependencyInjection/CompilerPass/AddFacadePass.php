<?php

namespace Local\Bundles\FacadeBundle\DependencyInjection\CompilerPass;

use Local\Bundles\FacadeBundle\Services\AbstractFacade;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddFacadePass
 * @package Local\Bundles\FacadeBundle\DependencyInjection\CompilerPass
 */
final class AddFacadePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container) : void
    {
        $facades = [];

        foreach ($container->findTaggedServiceIds('laravel.facade') as $id => $attr) {
            $class = $container->getDefinition($id)->getClass();
            $class = $class ?? $id;

            if (!is_subclass_of($class, AbstractFacade::class)) {
                throw new InvalidArgumentException(sprintf('The service "%s" must extend AbstractFacade.', $class));
            }

            $r = new ReflectionMethod($class, 'getFacadeAccessor');
            $r->setAccessible(true);
            $ref = $r->invoke(null);

            if (!\is_string($ref)) {
                throw new InvalidArgumentException(
                    sprintf('Facade accessor must be string, "%s" given.', \is_object($ref) ? \get_class($ref) : \gettype($ref))
                );
            }

            $ref = strpos($ref, '@') === 0 ? substr($ref, 1) : $ref;
            $facades[$id] = new Reference($ref);
        }

        $container->setAlias('laravel.facade.container', new Alias(ServiceLocatorTagPass::register($container, $facades), true));
    }
}
