<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces;

/**
 * Interface InstagramDataTransformerInterface
 * @package Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces
 *
 * @since 05.12.2020
 */
interface InstagramDataTransformerInterface
{
    /**
     * Обработка полученных картинок.
     *
     * @param array   $arDataFeed Данные фида.
     * @param integer $count      Ограничение по количеству картинок.
     *
     * @return array
     */
    public function processMedias(array $arDataFeed, int $count = 3): array;

    /**
     * Получить "курсор" следующей страницы. Пусто, если следующей страницы нет.
     *
     * @param array $arDataFeed Данные фида.
     *
     * @return string
     */
    public function getNextPageCursor(array $arDataFeed) : string;
}
