<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Interface OnControllerRequestHandlerInterface
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 */
interface OnControllerRequestHandlerInterface
{
    /**
     * Обработчик события kernel.controller.
     *
     * @param ControllerEvent $event Объект события.
     */
    public function handle(ControllerEvent $event): void;
}
