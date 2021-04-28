<?php

namespace Local\Facades;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class FormFacade
 * @package Local\Facades
 */
class FormFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'form.manager';
    }
}
