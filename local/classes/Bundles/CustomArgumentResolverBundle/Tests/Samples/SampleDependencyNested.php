<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Samples;

/**
 * Class SampleDependencyNested
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Samples
 */
class SampleDependencyNested
{
    /**
     * @var SampleDependency
     */
    private $e;
    /**
     * @var string
     */
    private $text;

    public function __construct(
        SampleDependency $e,
        string $text = ''
    ) {
        $this->e = $e;
        $this->text = $text;
    }

    public function nested(): int
    {
        return $this->e->get();
    }
}
