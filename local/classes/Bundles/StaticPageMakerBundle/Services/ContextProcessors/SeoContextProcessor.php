<?php

namespace Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors;

use Local\Bundles\StaticPageMakerBundle\Services\AbstractContextProcessor;
use Local\Bundles\StaticPageMakerBundle\Services\Bitrix\SeoMetaElement;
use RuntimeException;

/**
 * Class SeoContextProcessor
 * @package Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors
 *
 * @since 23.01.2021
 */
class SeoContextProcessor extends AbstractContextProcessor
{
    /**
     * @var SeoMetaElement $seoMetaDataSearcher Сборщик SEO информации элемента.
     */
    private $seoMetaDataSearcher;

    /**
     * SeoContextProcessor constructor.
     *
     * @param SeoMetaElement $seoMetaDataSearcher Сборщик SEO информации элемента.
     */
    public function __construct(SeoMetaElement $seoMetaDataSearcher)
    {
        $this->seoMetaDataSearcher = $seoMetaDataSearcher;
    }

    /**
     * @inheritDoc
     */
    public function handle() : array
    {
        if (!array_key_exists('url', $this->context) || !$this->context['url']) {
            return $this->context;
        }

        try {
            $this->seoMetaDataSearcher->data($this->context['url']);
        } catch (RuntimeException $e) {
            return $this->context;
        }

        $this->context['title'] = $this->seoMetaDataSearcher->title();
        $this->context['description'] = $this->seoMetaDataSearcher->description();

        return $this->context;
    }
}
