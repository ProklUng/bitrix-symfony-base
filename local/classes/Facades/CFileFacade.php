<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class CFileFacade
 * @package Local\Facades
 */
class CFileFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'CFile.wrapper';
    }
}
