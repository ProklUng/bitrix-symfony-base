<?php

namespace Local\SymfonyEvents\Handlers;

use Local\Facades\LastModifiedFacade;
use Local\SymfonyEvents\Events\ComponentEpilogEvent;

/**
 * Class LastModifiedHandler
 * @package Local\SymfonyEvents\Handlers
 */
class OnComponentEpilogLastModified
{
    /**
     * Слушатель события on.component.epilog. Установка LastModified заголовков.
     *
     * @param ComponentEpilogEvent $event.
     *
     * @return mixed
     */
    public function action(ComponentEpilogEvent $event)
    {
        // LastModified.
        if (empty($event->payload('timestamp'))
            &&
            empty($event->payload('timestamp_raw'))
        ) {
            return null;
        }

        // Уникализация ключа в глобальном состоянии LastModifier.
        $salt = md5($event->payload('templateName') . $event->payload('componentPath'));

        $timestamp = !empty($event->payload('timestamp_raw')) ?
            $event->payload('timestamp_raw') : MakeTimeStamp($event->payload('timestamp'));

        /** @noinspection PhpUndefinedMethodInspection */
        // Установить timestamp.
        LastModifiedFacade::add(
            $salt,
            $timestamp
        );

        return true;
    }
}
