<?php

namespace Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors;


use Local\Bundles\StaticPageMakerBundle\Services\AbstractContextProcessor;

/**
 * Class ExampleContextProcessor
 * Пример процессора контекста.
 * @package Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors
 *
 * @since 02.11.2020
 */
class ExampleContextProcessor extends AbstractContextProcessor
{
    /**
     * @inheritDoc
     */
    public function handle() : array
    {
        $this->context['processor_change'] = 'I do';

        return $this->context;
    }
}
