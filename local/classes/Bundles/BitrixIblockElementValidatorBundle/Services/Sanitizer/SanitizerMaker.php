<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\Services\Sanitizer;

use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Contracts\SanitizerInterface;
use Waavi\Sanitizer\Sanitizer;

/**
 * Class SanitizerMaker
 * @package Local\Bundles\BitrixIblockElementValidatorBundle\Services\Sanitizer
 *
 * @since 07.09.2020
 */
class SanitizerMaker implements SanitizerInterface
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
