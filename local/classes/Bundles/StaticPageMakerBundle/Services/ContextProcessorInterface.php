<?php

namespace Local\Bundles\StaticPageMakerBundle\Services;

/**
 * Interface ContextProcessorInterface
 * @package Local\Bundles\StaticPageMakerBundle\Services
 *
 * @since 03.11.2020
 */
interface ContextProcessorInterface
{
    /**
     * Задать контекст.
     *
     * @param array $context Контекст.
     *
     * @return self
     */
    public function setContext(array $context) : self;

    /**
     * Обработать контекст.
     *
     * @return array Обработанный контекст.
     */
    public function handle() : array;
}
