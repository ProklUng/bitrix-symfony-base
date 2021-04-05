<?php

namespace Local\Services\Sanitizing;

/**
 * Interface SanitizerInterface
 * @package Local\Services\Sanitizing
 *
 * @since 08.09.2020
 */
interface SanitizerInterface
{
    /**
     * Создать экземпляр Sanitizer.
     *
     * @param array $arData Данные.
     * @param array $rules  Схема санации.
     *
     * @return mixed
     */
    public function make(array $arData, array $rules);
}
