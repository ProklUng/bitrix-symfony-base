<?php

namespace Local\Services\Validation\Laravel;

/**
 * Class DefaultValidationMessages
 * @package Local\Services\Validation\Laravel
 */
class DefaultValidationMessages
{
    /**
     * @return string[]
     */
    public static function getItems(): array
    {
        return  [
            'required' => 'Не указано поле :attribute',
            'numeric' => 'Поле :attribute должно содержать числовое значение',
            'string' => 'Поле :attribute должно содержать строкове значение',
        ];
    }
}
