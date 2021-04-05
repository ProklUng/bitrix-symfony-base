<?php

namespace Local\Bundles\StaticPageMakerBundle\Services;

/**
 * Class AbstractContextProcessor
 * @package Local\Bundles\StaticPageMakerBundle\Services
 *
 * @since 02.11.2020
 */
abstract class AbstractContextProcessor implements ContextProcessorInterface
{
    /**
     * @var array $context Контекст.
     */
    protected $context = [];

    /**
     * @inheritDoc
     */
    public function setContext(array $context) : self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @inheritDoc
     */
    abstract public function handle() : array;

}
