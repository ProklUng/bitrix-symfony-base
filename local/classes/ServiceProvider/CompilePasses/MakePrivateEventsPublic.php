<?php

namespace Local\ServiceProvider\CompilePasses;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MakePrivateEventsPublic
 * Сделать все приватные подписчики событий публичными.
 * @package Local\ServiceProvider\CompilePasses
 *
 * @since 19.11.2020
 * @since 05.04.2021 Публичными делаюься и слушатели ядра.
 */
class MakePrivateEventsPublic implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container) : void
    {
        $this->makePublic($container, 'kernel.event_subscriber');
        $this->makePublic($container, 'kernel.event_listener');
    }

    /**
     * @param ContainerBuilder $container Контейнер.
     * @param string           $nameTag   Название тэга.
     *
     * @return void
     */
    private function makePublic(ContainerBuilder $container, string $nameTag) : void
    {
        $taggedServices = $container->findTaggedServiceIds(
            $nameTag
        );

        foreach ($taggedServices as $id => $service) {
            $def = $container->getDefinition($id);
            $def->setPublic(true);
        }
    }
}
