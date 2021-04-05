<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\InjectorController;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Interface InjectorControllerInterface
 * Интерфейс инжекции параметров в контроллеры.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\InjectorController
 * @codeCoverageIgnore
 *
 * @since 08.10.2020 Сеттер контейнера.
 */
interface InjectorControllerInterface
{
    /**
     * Инжекция аргументов в контроллер.
     *
     * @param ControllerEvent $event
     *
     * @return ControllerEvent
     */
    public function inject(ControllerEvent $event) : ControllerEvent;

    /**
     * Сеттер сервис-контейнера.
     *
     * @param ContainerInterface|null $container Контейнер.
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null);
}
