<?php

namespace Local\Facades;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class ResizerImageHardCrop
 * @package Local\Facades
 *
 * @method static setImageId(int $idImage) : self
 */
class ResizerImageHardCropFacade extends AbstractFacade
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
