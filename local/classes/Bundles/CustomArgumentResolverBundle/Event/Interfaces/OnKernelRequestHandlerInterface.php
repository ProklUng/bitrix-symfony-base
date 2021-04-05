<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Interface OnKernelRequestHandlerInterface
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces
 *
 * @since 10.09.2020
 */
interface OnKernelRequestHandlerInterface
{
    /**
     * Обработчик события kernel.request.
     *
     * @param RequestEvent $event Объект события.
     */
    public function handle(RequestEvent $event): void;
}
