<?php

namespace Local\Services\Bitrix;

use CMain;

/**
 * Class GetApplication
 * $APPLICATION для инжекции в сервисы и контроллеры.
 * @package Local\Services\Bitrix
 *
 * @since 11.10.2020
 */
class GetApplication
{
    /**
     * $APPLICATION.
     *
     * @return CMain
     */
    public function instance() : CMain
    {
        global $APPLICATION;

        return $APPLICATION;
    }
}