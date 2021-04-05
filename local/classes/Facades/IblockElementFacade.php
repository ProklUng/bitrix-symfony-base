<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class IblockElementFacade
 * @package Local\Facades
 */
class IblockElementFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'iblock.element.manager';
    }
}
