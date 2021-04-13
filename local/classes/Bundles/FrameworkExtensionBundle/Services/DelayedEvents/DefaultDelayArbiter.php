<?php

namespace Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class DefaultDelayArbiter
 * @package Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents
 *
 * @since 13.04.2021
 */
class DefaultDelayArbiter
{
    /**
     * Определяет - реализует ли событие интерфейс Delayable.
     *
     * @param Event       $event     Payload события.
     * @param string|null $eventName Событие.
     *
     * @return boolean
     */
    public function __invoke(Event $event, ?string $eventName = null) : bool
    {
        $interfaces = class_implements($event);

        return in_array(Delayable::class, $interfaces, true);
    }
}
