<?php

namespace Local\Bundles\FacadeBundle\Tests\Fixture\Facades;

use Local\Bundles\FacadeBundle\Services\AbstractFacade;
use Local\Bundles\FacadeBundle\Tests\Fixture\Services\FooService;

/**
 * Class RealFacade
 * @package Local\Bundles\FacadeBundle\Tests\Fixture\Facades
 */
class RealFacade extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor() : string
    {
        return 'request_stack';
    }
}
