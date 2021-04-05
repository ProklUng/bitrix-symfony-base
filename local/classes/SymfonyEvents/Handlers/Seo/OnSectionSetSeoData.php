<?php

namespace Local\SymfonyEvents\Handlers\Seo;

use Local\Facades\IblockFacade;
use Local\SymfonyEvents\Events\ComponentEpilogEvent;

/**
 * Class OnSectionSetSeoData
 * @package Tests\App\Events\Samples\Handlers\Seo
 */
class OnSectionSetSeoData
{
    /**
     * Слушатель события set.seo.section.page. Установка title & description.
     *
     * @param ComponentEpilogEvent $event Объект события.
     *
     * @return void
     */
    public function action(ComponentEpilogEvent $event): void
    {
        if (empty($event->arResult())) {
            return;
        }

        $title = $this->title($event);
        $description = $this->description($event);

        $this->setSeoMetaTags($title, $description);
    }

    /**
     * Title.
     *
     * @param ComponentEpilogEvent $event
     *
     * @return string
     */
    protected function title(ComponentEpilogEvent $event) : string
    {
        $arParams = $event->arParams();

        if (!empty($GLOBALS['APPLICATION']->GetPageProperty('title'))) {
            return $GLOBALS['APPLICATION']->GetPageProperty('title');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $title = IblockFacade::getNameByIdCached($arParams['IBLOCK_ID']);

        return !empty($title) ? $title : '';
    }

    /**
     * Description.
     *
     * @param ComponentEpilogEvent $event
     *
     * @return string
     */
    protected function description(ComponentEpilogEvent $event) : string
    {
        $arParams = $event->arParams();

        if (!empty($GLOBALS['APPLICATION']->GetPageProperty('description'))) {
            return $GLOBALS['APPLICATION']->GetPageProperty('description');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $description = IblockFacade::getDescriptionByIdCached($arParams['IBLOCK_ID']);

        return !empty($description) ? $description :  '';
    }

    /**
     * Установить мета тэги.
     *
     * @param string $title
     * @param string $description
     *
     * @return void
     */
    private function setSeoMetaTags(string $title, string $description) : void
    {
        if (!empty($title)) {
            $GLOBALS['APPLICATION']->SetPageProperty('title', $title);
        }

        if (!empty($description)) {
            $GLOBALS['APPLICATION']->SetPageProperty('description', $description);
        }
    }
}
