<?php
// Инициализация событий Symfony.

use Prokl\BitrixOrdinaryToolsBundle\Services\SymfonyEvents\CustomEvents;

if (class_exists(CustomEvents::class)) {
    /** @var CustomEvents $symfonyEvents */
    $symfonyEvents = container()->get('bitrix_ordinary_tools.custom_event_dispatcher');
// Слушатели по умолчанию.
    $symfonyEvents->applyListeners();
}

