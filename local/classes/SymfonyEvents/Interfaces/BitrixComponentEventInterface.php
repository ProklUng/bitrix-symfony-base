<?php

namespace Local\SymfonyEvents\Interfaces;

/**
 * Interface BitrixComponentEventInterface
 * @package Local\SymfonyEvents\Interfaces
 */
interface BitrixComponentEventInterface
{
    /**
     * $arResult.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function arResult(string $key = null);

    /**
     * $arParams.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function arParams(string $key = null);

    /**
     * Дополнительные параметры.
     *
     * @param string|null $key Ключ.
     *
     * @return mixed
     */
    public function payload(string $key = null);

    /**
     * Установить $arParams.
     *
     * @param array       $arParams $arParams.
     * @param string|null $key      Ключ.
     * @param null        $value    Данные.
     *
     * @return void
     */
    public function setParams(array $arParams, string $key = null, $value = null) : void;

    /**
     * Установить $arResult.
     *
     * @param array       $arResult $arResult.
     * @param string|null $key      Ключ.
     * @param null        $value    Данные.
     *
     * @return void
     */
    public function setResult(array $arResult, string $key = null, $value = null) : void;
}
