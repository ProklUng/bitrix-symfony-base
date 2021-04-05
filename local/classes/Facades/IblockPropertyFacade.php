<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class IblockPropertyFacade
 * @package Local\Facades
 */
class IblockPropertyFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'iblock.property.manager';
    }
}
