<?php

namespace Local\Facades;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class TimestampFacade
 * @package Local\Facades
 */
class TimestampFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'timestamp.news';
    }
}
