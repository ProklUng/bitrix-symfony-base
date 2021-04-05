<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces;

use Exception;

/**
 * Interface RetrieverInstagramDataInterface
 * @package Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces
 *
 * @since 05.12.2020
 * @since 08.12.2020 Доработка.
 */
interface RetrieverInstagramDataInterface
{
    /**
     * Запрос.
     *
     * @return array
     * @throws Exception
     */
    public function query() : array;

    /**
     * @param string $userId Instagram ID user.
     *
     * @return RetrieverInstagramDataInterface
     */
    public function setUserId(string $userId): self;

    /**
     * Сеттер after. Постраничное получение.
     *
     * @param string $after Параметр after из https://rapidapi.com/restyler/api/instagram40.
     *
     * @return RetrieverInstagramDataInterface
     */
    public function setAfterMark(string $after): self;

    /**
     * Сеттер количества картинок.
     *
     * @param integer $count Количество картинок.
     *
     * @return RetrieverInstagramDataInterface
     */
    public function setCount(int $count): self;

    /**
     * @param boolean $useMock     Использовать мок.
     * @param string  $fixturePath Путь к фикстуре.
     *
     * @return RetrieverInstagramDataInterface
     */
    public function setUseMock(bool $useMock, string $fixturePath = ''): self;
}
