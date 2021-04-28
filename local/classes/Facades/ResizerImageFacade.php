<?php

namespace Local\Facades;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class ResizerImageFacade
 * @package Local\Facades
 *
 * @method static setImageId(int $idImage) : self
 * @method static setWidth(int $width) : self
 */
class ResizerImageFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'image.resizer';
    }
}
