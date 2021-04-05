<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction;

/**
 * Interface IblockPropertyTypeNativeInterface
 * Интерфейс для нативного способа описания кастомных полей.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction
 */
interface IblockPropertyTypeNativeInterface
{
    /**
     * Инициализирует тип свойства, добавляя вызов getUserTypeDescription() при событии
     * iblock::OnIBlockPropertyBuildList
     *
     * @return void
     */
    public function init() : void;
}
