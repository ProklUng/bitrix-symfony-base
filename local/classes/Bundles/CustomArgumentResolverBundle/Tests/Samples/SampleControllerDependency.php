<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Samples;
/**
 * Class SampleControllerDependency
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests
 * @codeCoverageIgnore
 */
class SampleControllerDependency
{
    public function get(): int
    {
        return 2;
    }
}
