<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Samples;

/**
 * Class SampleServiceNested
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Samples
 */
class SampleServiceNested
{
    protected $ob;

    public function __construct(SampleDependencyNested $ob)
    {
        $this->ob = $ob;
    }

    public function nested() : int
    {
        return $this->ob->nested();
    }
}
