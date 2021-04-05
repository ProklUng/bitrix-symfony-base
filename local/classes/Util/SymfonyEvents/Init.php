<?php

namespace Local\Util\SymfonyEvents;

/**
 * Class Init
 * @package Local\Util\SymfonyEvents
 */
class Init
{
    /**
     * Init constructor.
     *
     * @param Events $obEventsConfig Загрузчик конфигов.
     */
    public function __construct(
        Events $obEventsConfig
    ) {
        // Инициализировать слушателей событий из конфига.
        $obEventsConfig->applyListeners();
    }
}
