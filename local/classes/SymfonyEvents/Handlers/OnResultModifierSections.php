<?php

namespace Local\SymfonyEvents\Handlers;

use Local\Facades\TimestampFacade;
use Local\SymfonyEvents\Events\ResultModifierSectionsEvent;

/**
 * Class onResultModifierSections
 * @package Local\SymfonyEvents\Handlers
 */
class OnResultModifierSections
{
    /**
     * Слушатель события on.result.modifier.component. Вычисление последнего измененного элемента.
     *
     * @param ResultModifierSectionsEvent $event Объект данных события.
     *
     * @return mixed
     */
    public function action(ResultModifierSectionsEvent $event): void
    {
        $arResult = $event->arResult();
        // ITEMS или SECTIONS
        $arData = $this->getData($arResult);

        $arResult['LAST_MODIFIED'] = '';
        if (!empty($arData)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $arResult['LAST_MODIFIED'] = TimestampFacade::setTimestamps($arData)->getNewestTimestamp();
        }

        $event->setResult($arResult);
    }

    /**
     * ITEMS или SECTIONS.
     *
     * @param array $arResult
     *
     * @return array
     */
    protected function getData(array $arResult) : array
    {
        $result = [];

        if (!empty($arResult['ITEMS'])) {
            $result = $arResult['ITEMS'];
        }

        if (!empty($arResult['SECTIONS'])) {
            $result = $arResult['SECTIONS'];
        }

        return $result;
    }
}
