<?php

namespace Local\Bundles\FacadeBundle\Tests\Fixture\Facades;

/**
 * Class InvalidFacadeAccessor
 * @package Local\Bundles\FacadeBundle\Tests\Fixture\Facades
 */
class InvalidFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor() : string
    {
        return 'array';
    }
}
