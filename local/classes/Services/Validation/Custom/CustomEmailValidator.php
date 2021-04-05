<?php

namespace Local\Services\Validation\Custom;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class CustomEmailValidator
 * @package Local\Services\Validation\Custom
 *
 * @since 08.09.2020
 */
class CustomEmailValidator implements Rule
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
        $validator = new EmailValidator();

        return $validator->isValid($value, new RFCValidation());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return ':attribute should be a RFC valid email.';
    }
}
