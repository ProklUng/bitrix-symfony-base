<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('prokl.ckeditor',
    [
        'Prokl\Ckeditor\MediaLib' => 'lib/medialib.php',
        'Prokl\Ckeditor\EventCK' => 'lib/eventck.php',
    ]
);
