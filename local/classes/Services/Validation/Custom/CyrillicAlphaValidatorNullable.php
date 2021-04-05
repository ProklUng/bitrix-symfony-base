<?php

namespace Local\Services\Validation\Custom;

/**
 * Class CyrillicAlphaValidator
 * @package Local\Services\Validation\Custom
 *
 * @since 17.10.2020
 */
class CyrillicAlphaValidatorNullable extends CyrillicAlphaValidator
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
