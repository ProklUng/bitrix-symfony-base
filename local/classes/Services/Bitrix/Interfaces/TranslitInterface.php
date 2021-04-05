<?php

namespace Local\Services\Bitrix\Interfaces;

/**
 * Interface TranslitInterface
 * @package Local\Services\Bitrix\Interfaces
 *
 * @since 08.09.2020
 */
interface TranslitInterface
{
    /**
     * Транслитировать строку.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public function transform(string $value) : string;
}
