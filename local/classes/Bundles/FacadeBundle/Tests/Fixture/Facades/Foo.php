<?php

namespace Local\Bundles\FacadeBundle\Tests\Fixture\Facades;

use Local\Bundles\FacadeBundle\Services\AbstractFacade;
use Local\Bundles\FacadeBundle\Tests\Fixture\Services\FooService;

/**
 * Class Foo
 * @package Local\Bundles\FacadeBundle\Tests\Fixture\Facades
 */
class Foo extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor() : string
    {
        return FooService::class;
    }
}
