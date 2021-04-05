<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class CUser
 * @package Local\Facades
 */
class CUser  extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor() : string
    {
        return 'CUser';
    }
}
