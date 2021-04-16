<?php

namespace Local\Bundles\FacadeBundle\Tests\Fixture\Services;

/**
 * Class FooService
 * @package Local\Bundles\FacadeBundle\Tests\Fixture\Services
 *
 * @since 15.04.2021
 */
class FooService
{
    public function sayHello()
    {
        return 'hello';
    }

    public function callWithArgs($bar, $foo)
    {
        return [$bar, $foo];
    }
}
