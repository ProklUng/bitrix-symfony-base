<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class ResizerImageUpscaleFacade
 * @package Local\Facades
 */
class ResizerImageUpscaleFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'image.resizer.upscale';
    }
}
