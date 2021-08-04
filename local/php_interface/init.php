<?php

// composer autoload и dotenv подключаются в файлах конфигурации ядра
// bitrix/.settings.php и bitrix/php_interface/dbconn.php
// которые в свою очередь можно обновить, отредактировав данные в директории /environments/
// и "перезагрузить" командой `./vendor/bin/jedi env:init default`

use Bitrix\Main\Loader;
use Prokl\ServiceProvider\ServiceProvider;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Prokl\CollectionExtenderBundle\Services\Extender;

Loader::includeModule('iblock');

// Csrf токен приложения. До всякого кэширования.
$csrf = new CsrfTokenManager();
$appCsrfToken = $csrf->getToken('app')->getValue();
$_SESSION['csrf_token'] = $appCsrfToken;

// так как  автолоад (в нашем случае) регистрируется до ядра,
// Твиг не успевает зарегистрироваться
// необходимо это действие повтроить еще раз:

maximasterRegisterTwigTemplateEngine();

Arrilot\BitrixModels\ServiceProvider::register();
Arrilot\BitrixModels\ServiceProvider::registerEloquent();

// Во избежании проблем с созданием кэша на проде
// @see https://symfony.com/doc/current/setup/file_permissions.html
umask(0000);

// Symfony сервис-провайдер
$symfonyServiceProvider = new ServiceProvider(
    'local/configs/services.yaml'
);

// Макросы для коллекций Laravel.
container()->get(Extender::class);

include_once 'events.php';
include_once 'symfony_events.php';

