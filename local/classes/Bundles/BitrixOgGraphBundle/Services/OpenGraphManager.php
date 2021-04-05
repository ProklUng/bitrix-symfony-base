<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services;

use Astrotomic\OpenGraph\OpenGraph;
use Astrotomic\OpenGraph\Type;

/**
 * Class OpenGraphManager
 * @package Local\Bundles\BitrixOgGraphBundle\Services
 *
 * @since 13.10.2020
 * @since 15.01.2021 Избавление от венгерской нотации.
 * @since 20.02.2021 Доработка.
 */
class OpenGraphManager
{
    /**
     * @var OgDTO $dtoOpenGraph DTO с данными.
     */
    private $dtoOpenGraph;

    /**
     * @var OpenGraph $openGraph
     */
    private $openGraph;

    /**
     * @var bool[] $mapOgEntity Карта сущностей OG для фабрики.
     */
    private $mapOgEntity = [
        'website' => true, 'article' => true
    ];

    /**
     * @var Type $ogEntity Тип - пост, вебсайт.
     */
    private $ogEntity;

    /**
     * OpenGraphManager constructor.
     *
     * @param OpenGraph $openGraph OpenGraph manager.
     */
    public function __construct(OpenGraph $openGraph)
    {
        $this->openGraph = $openGraph;
        $this->ogEntity = $this->openGraph::website();
    }

    /**
     * Движуха.
     *
     * @return string
     */
    public function go(): string
    {
        $this->factoryTypeOgEntity($this->dtoOpenGraph->type);

        if ($this->dtoOpenGraph->article_publisher) {
            $this->ogEntity->addProperty('article', 'publisher', $this->dtoOpenGraph->article_publisher);
        }

        if ($this->dtoOpenGraph->timePublished) {
            $this->ogEntity->addProperty('article', 'published_time', $this->dtoOpenGraph->timePublished);
        }

        if ($this->dtoOpenGraph->fb_admins) {
            $this->ogEntity->addProperty('fb', 'admins', $this->dtoOpenGraph->fb_admins);
        }

        if ($this->dtoOpenGraph->site_name) {
            $this->ogEntity->siteName($this->dtoOpenGraph->site_name);
        }

        if ($this->dtoOpenGraph->title) {
            $this->ogEntity->title($this->dtoOpenGraph->title);
        }

        if ($this->dtoOpenGraph->url) {
            $this->ogEntity->url($this->dtoOpenGraph->url);
        }

        if ($this->dtoOpenGraph->img) {
            $this->ogEntity->image($this->dtoOpenGraph->img);
        }

        if ($this->dtoOpenGraph->description) {
            $this->ogEntity->description($this->dtoOpenGraph->description);
        }

        return $this->ogEntity->locale('ru');
    }

    /**
     * Задать DTO.
     *
     * @param OgDTO $dtoOpenGraph DTO с данными.
     *
     * @return static
     */
    public function setDto(OgDTO $dtoOpenGraph): self
    {
        $this->dtoOpenGraph = $dtoOpenGraph;

        return $this;
    }

    /**
     * Фабрика OG сущностей.
     *
     * @param string $type Тип.
     *
     * @return void
     */
    private function factoryTypeOgEntity(string $type) : void
    {
        // @phpstan-ignore-next-line
        if (!empty($this->mapOgEntity[$type])) {
            $this->ogEntity = $this->openGraph->{$type}();
        }
    }
}
