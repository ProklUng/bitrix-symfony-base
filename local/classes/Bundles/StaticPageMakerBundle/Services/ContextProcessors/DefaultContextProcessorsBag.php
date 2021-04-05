<?php

namespace Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors;

use Local\Bundles\StaticPageMakerBundle\Services\ContextProcessorInterface;
use Traversable;

/**
 * Class DefaultContextProcessorsBag
 * @package Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors
 *
 * @since 23.01.2021
 */
class DefaultContextProcessorsBag
{
    /**
     * @var ContextProcessorInterface[] $processors
     */
    private $processors = [];

    /**
     * @param Traversable $processors
     *
     * @return void
     */
    public function setProcessors(Traversable $processors)
    {
        $handlers = iterator_to_array($processors);

        $this->processors = $handlers;
    }

    /**
     * @return ContextProcessorInterface[]
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }
}