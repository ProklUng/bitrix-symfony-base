<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class ResizerImageNoUpscaleFacade
 * @package Local\Facades
 */
class ResizerImageNoUpscaleFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'image.resizer.no.upscale';
    }
}
