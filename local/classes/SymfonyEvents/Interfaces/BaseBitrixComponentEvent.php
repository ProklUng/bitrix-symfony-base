<?php

namespace Local\SymfonyEvents\Interfaces;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class BaseBitrixComponentEvent
 * @package Local\SymfonyEvents\Interfaces;
 */
class BaseBitrixComponentEvent extends Event implements BitrixComponentEventInterface
{
    /**
     * @var array $arResult
     */
    private $arResult;
    /**
     * @var array $arParams
     */
    private $arParams;

    /**
     * @var array $arPayload Дополнительные параметры.
     */
    private $arPayload;

    /**
     * BaseBitrixComponentEvent constructor.
     *
     * @param array $arResult
     * @param array $arParams
     * @param array $arPayload
     */
    public function __construct(
        array $arResult = [],
        array $arParams = [],
        array $arPayload = []
    ) {
        $this->arResult = $arResult;
        $this->arParams = $arParams;
        $this->arPayload = $arPayload;
    }

    /**
     * $arResult.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function arResult(string $key = null)
    {
        if ($key === null) {
            return $this->arResult;
        }

        return !empty($this->arResult[$key]) ? $this->arResult[$key] : null;
    }

    /**
     * $arParams.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function arParams(string $key = null)
    {
        if ($key === null) {
            return $this->arParams;
        }

        return !empty($this->arParams[$key]) ? $this->arParams[$key] : null;
    }

    /**
     * Нагрузка.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function payload(string $key = null)
    {
        if ($key === null) {
            return $this->arPayload;
        }

        return !empty($this->arPayload[$key]) ? $this->arPayload[$key] : null;
    }

    /**
     * Установить $arParams.
     *
     * @param array       $arParams Данные.
     * @param string|null $key      Ключ.
     * @param null        $value    Данные.
     *
     * @return void
     */
    public function setParams(array $arParams, string $key = null, $value = null) : void
    {
        if ($key !== null) {
            $this->arParams[$key] = $value;
            return;
        }

        $this->arParams = $arParams;
    }

    /**
     * Установить $arResult.
     *
     * @param array       $arResult $arResult.
     * @param string|null $key      Ключ.
     * @param null        $value    Данные.
     *
     * @return void
     */
    public function setResult(array $arResult, string $key = null, $value = null) : void
    {
        if ($key !== null) {
            $this->arResult[$key] = $value;
            return;
        }

        $this->arResult = $arResult;
    }
}
