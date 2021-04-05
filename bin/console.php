<?php

use Local\Commands\Utils\LoaderBitrix;
use Local\Services\Console\ConsoleCommandConfigurator;

@set_time_limit(0);

$_SERVER['DOCUMENT_ROOT'] = __DIR__. DIRECTORY_SEPARATOR . '..';
$GLOBALS['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

$autoloadPath = $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

/** @noinspection PhpIncludeInspection */
require_once $autoloadPath;

/**
 * Загрузить Битрикс.
 */
$loaderBitrix = new LoaderBitrix();
$loaderBitrix->setDocumentRoot($_SERVER['DOCUMENT_ROOT']);
$loaderBitrix->initializeBitrix();

if (!$loaderBitrix->isBitrixLoaded()) {
    exit('Bitrix not initialized.');
}

if (!container()->has('console.command.manager')) {
    exit('Service console.command.manager not registered.');
}

/**
 * @var ConsoleCommandConfigurator $consoleCommandManager
 */
$consoleCommandManager = container()->get('console.command.manager')
    ->init();

$consoleCommandManager->run();
