<?php

namespace Local\Bundles\CkEditorBundle\Services;

use Bitrix\Main\Page\Asset;

/**
 * Class EventCK
 * @package Prokl\Ckeditor
 */
class EventCK
{
    /**
     * Подключение ассетов.
     *
     * @return void
     */
    public function register() : void
    {
        if (!defined('ADMIN_SECTION')) {
            return;
        }

        Asset::getInstance()->addJs('/local/classes/Bundles/CkEditorBundle/src/build/ckeditor.js');
        Asset::getInstance()->addJs('/local/classes/Bundles/CkEditorBundle/Assets/script.js');
        Asset::getInstance()->addCss('/local/classes/Bundles/CkEditorBundle/Assets/style.css');
    }
}