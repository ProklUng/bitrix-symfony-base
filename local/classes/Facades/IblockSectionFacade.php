<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class IblockSectionFacade
 * @package Local\Facades
 */
class IblockSectionFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'iblock.section.manager';
    }
}
