<?php

namespace Local\Services\Validation\Custom;

/**
 * Class CustomEmailValidatorNullable
 * Валидатор email, но с возможностью пустого значения.
 * @package Local\Services\Validation\Custom
 *
 * @since 08.10.2020
 */
class CustomEmailValidatorNullable extends CustomEmailValidator
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
        if ($value === null || $value === '') {
            return true;
        }

        return parent::passes($attribute, $value);
    }
}
