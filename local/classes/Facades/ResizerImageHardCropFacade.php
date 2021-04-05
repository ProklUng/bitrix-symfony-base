<?php

namespace Local\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class ResizerImageHardCrop
 * @package Local\Facades
 */
class ResizerImageHardCropFacade extends Facade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'image.resizer.hard.crop';
    }
}
