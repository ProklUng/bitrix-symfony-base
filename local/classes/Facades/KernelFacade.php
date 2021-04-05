<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class KernelFacade
 * @package Local\Facades
 */
class KernelFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'app.options';
    }
}
