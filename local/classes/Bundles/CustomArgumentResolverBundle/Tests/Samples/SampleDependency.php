<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Samples;

/**
 * Class SampleDependency
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Samples
 * @codeCoverageIgnore
 */
class SampleDependency
{
    public function get(): int
    {
        return 222;
    }
}
