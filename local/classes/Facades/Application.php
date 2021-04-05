<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class Application
 * @package Local\Facades
 */
class Application extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor() : string
    {
        return 'Bitrix\Main\Application';
    }
}
