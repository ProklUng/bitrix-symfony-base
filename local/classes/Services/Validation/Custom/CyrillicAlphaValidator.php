<?php

namespace Local\Services\Validation\Custom;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class CyrillicAlphaValidator
 * @package Local\Services\Validation\Custom
 *
 * @since 17.10.2020
 */
class CyrillicAlphaValidator implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  mixed $attribute Аттрибут.
     * @param  mixed $value     Значение.
     *
     * @return null|boolean
     */
    public function passes($attribute, $value = null): ?bool
    {
        return is_string($value) && preg_match('/^[\sA-Za-zА-Яа-яЁё]+$/u', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Для :attribute доступны только кириллические символы.';
    }
}
