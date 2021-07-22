<?php

return array (
    'maximaster' => array(
        'value' => array(
            'tools' => array(
                'twig' => array(
                    // Режим отладки выключен
                    'debug' => env('DEBUG', false),

                    //Кодировка
                    'charset' => 'UTF-8',

                    //кеш хранится в уникальной директории. Должен быть полный абсолютный путь
                    'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/maximaster/tools.twig',

                    //Автообновление включается только в момент очистки кеша ИЛИ в режиме дебага
                    'auto_reload' => ((isset($_GET[ 'clear_cache' ]) && strtoupper($_GET[ 'clear_cache' ]) == 'Y')) || env('DEBUG', false),

                    //Автоэскейп отключен, т.к. битрикс по-умолчанию его сам делает
                    'autoescape' => false,

                    // Переменные arResult будут доступны не в result, а напрямую
                    'extract_result' => false,
                )
            )
        )
    ),
);
