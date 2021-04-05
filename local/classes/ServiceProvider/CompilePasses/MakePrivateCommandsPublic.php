<?php

namespace Local\ServiceProvider\CompilePasses;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MakePrivateEventsPublic
 * Сделать все приватные команды событий публичными.
 * @package Local\ServiceProvider\CompilePasses
 *
 * @since 20.12.2020
 */
class MakePrivateCommandsPublic implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(
            'console.command'
        );

        foreach ($taggedServices as $id => $service) {
            $def = $container->getDefinition($id);
            $def->setPublic(true);
        }
    }
}
