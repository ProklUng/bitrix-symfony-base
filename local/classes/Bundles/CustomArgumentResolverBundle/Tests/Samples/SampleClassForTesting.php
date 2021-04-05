<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Samples;

use Fedy\Logger\MyLogger;

/**
 * Class SampleClassForTesting
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Samples
 * @codeCoverageIgnore
 */
class SampleClassForTesting
{
    /**
     * @var MyLogger
     */
    private $logger;

    public function __construct(MyLogger $logger)
    {
        $this->logger = $logger;
    }

    public function check(): void
    {

    }
}
