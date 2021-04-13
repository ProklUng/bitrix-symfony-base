<?php

namespace Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents;

use Symfony\Contracts\EventDispatcher\Event;
use Throwable;

/**
 * Class DefaultDelayedEventExceptionHandler
 * Пример обработчика исключений, выкидываемых событием.
 * @package Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents
 *
 * @since 13.04.2021
 */
class DefaultDelayedEventExceptionHandler
{
    /**
     * @param Throwable $error Исключение.
     * @param string|null $eventName Название события.
     * @param Event|null $event Payload события.
     *
     * @return mixed
     * @throws Throwable
     */
    public function __invoke(Throwable $error, ?Event $event = null, ?string $eventName = null)
    {
        // Тут можно что-то сделать. Записать в лог и т.д.
        // return true;

        throw $error; // Стандартное поведение - пробросить исключение дальше.
    }
}
