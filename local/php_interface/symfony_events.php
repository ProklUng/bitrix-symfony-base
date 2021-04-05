<?php
// Инициализация событий Symfony.

use Local\Util\SymfonyEvents\Events;

$symfonyEvents = new Events();
// Слушатели по умолчанию.
$symfonyEvents->applyListeners();
