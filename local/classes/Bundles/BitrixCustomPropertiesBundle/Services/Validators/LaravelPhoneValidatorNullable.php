<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\Validators;

/**
 * Class LaravelPhoneValidatorNullable
 * Валидатор телефона, но с возможностью пустого значения.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\Validators
 *
 * @since 08.10.2020
 */
class LaravelPhoneValidatorNullable extends LaravelPhoneValidator
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  mixed $attribute Аттрибут.
     * @param  mixed $value     Значение.
     *
     * @return boolean
     */
    public function passes($attribute, $value): ?bool
    {
        if ($value === null || $value === '' || $value === 'null') {
            return true;
        }

        return parent::passes($attribute, $value);
    }
}
