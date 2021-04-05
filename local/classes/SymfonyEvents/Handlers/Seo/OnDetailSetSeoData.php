<?php

namespace Local\SymfonyEvents\Handlers\Seo;

use Local\SymfonyEvents\Events\ComponentEpilogEvent;

/**
 * Class OnDetailSetSeoData
 * @package Tests\App\Events\Samples\Handlers\Seo
 */
class OnDetailSetSeoData
{
    /**
     * Слушатель события set.seo.meta.tags. Установка title & description.
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
        $arResult = $event->arResult();

        if (empty($arResult['IPROPERTY_VALUES'])) {
            return (string)$arResult['NAME'];
        }

        $title = $arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE'];

        return !empty($title) ?
            $arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE']
            :  $arResult['NAME'];
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
        $arResult = $event->arResult();

        if (empty($arResult['IPROPERTY_VALUES'])) {
            return '';
        }

        $description = $arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION'];

        return !empty($description) ?
            $arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION']
            :  '';
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
