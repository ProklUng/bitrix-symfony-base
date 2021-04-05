<?php

namespace Local\SymfonyTools\Framework\Interfaces;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Interface InjectorControllerInterface
 * Интерфейс инжекции параметров в контроллеры.
 * @package Local\SymfonyTools\Framework\Interfaces
 * @codeCoverageIgnore
 *
 * @since 05.09.2020
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
}
