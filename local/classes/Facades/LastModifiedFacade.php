<?php

namespace Local\Facades;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class LastModified
 * @package Local\Facades
 */
class LastModifiedFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'last.modified';
    }
}
