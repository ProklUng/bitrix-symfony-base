<?php

namespace Local\Services\Sanitizing;

use Waavi\Sanitizer\Sanitizer;

/**
 * Class SanitizerService
 * @package Local\Services\Sanitizing
 *
 * @since 07.09.2020
 */
class SanitizerService implements SanitizerInterface
{
    /**
     * Создать экземпляр Sanitizer.
     *
     * @param array $arData Данные.
     * @param array $rules  Схема санации.
     *
     * @return Sanitizer
     */
    public function make(array $arData, array $rules): Sanitizer
    {
        return new Sanitizer($arData, $rules);
    }
}
