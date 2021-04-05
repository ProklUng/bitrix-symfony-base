<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\Validators;

use Illuminate\Contracts\Validation\Rule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class LaravelPhoneValidator
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\Validators
 *
 * @since 07.09.2020
 * @since 07.10.2020 Локализация сообщения об ошибке.
 */
class LaravelPhoneValidator implements Rule
{
    /** @const string DEFAULT_COUNTRY Код страны по умолчанию. */
    private const DEFAULT_COUNTRY = 'RU';

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
        $defaultRegion = self::DEFAULT_COUNTRY;

        if (strpos($value, '+') === 0) {
            $defaultRegion = null;
        }

        if ($value === null || $value === '') {
            return false;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        $value = (string) $value;

        $phoneNumber = null;

        try {
            $phoneNumber = $phoneUtil->parse($value, $defaultRegion);
        } catch (NumberParseException $e) {
            return false;
        }

        $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);

        if ($phoneUtil->isValidNumber($phoneNumber) === false) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Номер телефона должен быть валидным.';
    }
}
