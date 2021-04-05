<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Local\Bundles\SymfonyMiddlewareBundle\Middleware\MiddlewareEnum;
use Local\Bundles\SymfonyMiddlewareBundle\ServiceLocator\MiddlewareServiceLocator;

final class ControllerMiddlewarePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $def = $container->getDefinition(MiddlewareServiceLocator::class);

        foreach ($container->findTaggedServiceIds(MiddlewareEnum::CONTROLLER_TAG) as $id => $attributes) {
            if (empty($attributes)) {
                throw new \InvalidArgumentException('Provide at least "middleware" attribute');
            }

            foreach ($attributes as $attribute) {
                if (!array_key_exists('middleware', $attribute)) {
                    throw new \InvalidArgumentException('No "middleware" attribute was found');
                }

                if (array_key_exists('action', $attribute)) {
                    $def->addMethodCall(
                        'addControllerActionMiddleware',
                        [$id, $attribute['action'], new Reference($attribute['middleware'])]
                    );
                } else {
                    $def->addMethodCall(
                        'addControllerMiddleware',
                        [$id, new Reference($attribute['middleware'])]
                    );
                }
            }
        }
    }
}
