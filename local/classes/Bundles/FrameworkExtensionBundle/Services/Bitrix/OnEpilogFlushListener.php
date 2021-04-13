<?php

namespace Local\Bundles\FrameworkExtensionBundle\Services\Bitrix;

use Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents\DelayedEventDispatcher;

/**
 * Class OnEpilogFlushListener
 * Запуск отложенных событий в самом конце жизненного цикла Битрикса.
 * @package Local\Bundles\FrameworkExtensionBundle\Services\Bitrix
 *
 * @since 13.04.2021
 */
class OnEpilogFlushListener
{
    /**
     * @var DelayedEventDispatcher $delayedEventDispatcher Диспетчер отложенных событий.
     */
    private $delayedEventDispatcher;

    /**
     * OnKernelTerminateRequestListener constructor.
     *
     * @param DelayedEventDispatcher $delayedEventDispatcher Диспетчер отложенных событий.
     */
    public function __construct(DelayedEventDispatcher $delayedEventDispatcher)
    {
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @return void
     */
    public function handle() : void
    {
        $this->delayedEventDispatcher->flush();
    }
}
