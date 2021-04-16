<?php

namespace Local\Bundles\FrameworkExtensionBundle\Event;

use Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents\DelayedEventDispatcher;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class FlushDelayedEventsListener
 * @package Local\Bundles\FrameworkExtensionBundle\Event
 *
 * @since 13.04.2021
 */
class FlushDelayedEventsListener
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
     * @param TerminateEvent $event Событие.
     *
     * @return void
     */
    public function handle(TerminateEvent $event) : void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->delayedEventDispatcher->flush();
    }
}
