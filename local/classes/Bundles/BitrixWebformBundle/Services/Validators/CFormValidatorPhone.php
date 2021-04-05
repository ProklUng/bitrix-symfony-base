<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class CFormValidatorPhone
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators
 *
 * @since 06.02.2021
 */
class CFormValidatorPhone extends AbstractCustomBitrixWebformValidator
{
    /** @const string DEFAULT_COUNTRY Код страны по умолчанию. */
    private const DEFAULT_COUNTRY = 'RU';

    /**
     * @var string $errorMessage
     */
    private $errorMessage = '#FIELD_NAME#: невалидный телефон';

    /**
     * @inheritDoc
     */
    public function GetDescription() : array
    {
        return [
            "NAME" => "custom_phone", // validator string ID
            "DESCRIPTION" => 'Валидация телефонного номера', // validator description
            "TYPES" => ["text", "textarea"], //  list of types validator can be applied.
            "HANDLER" => [$this, "DoValidate"] // main validation method
        ];
    }

    /**
     * @inheritDoc
     */
    public function DoValidate($arParams, $arQuestion, $arAnswers, $arValues): bool
    {
        global $APPLICATION;

        foreach ($arValues as $value)
        {
            $defaultRegion = self::DEFAULT_COUNTRY;

            if (strpos($value, '+') === 0) {
                $defaultRegion = null;
            }

            $phoneUtil = PhoneNumberUtil::getInstance();
            $value = (string) $value;

            $phoneNumber = null;

            try {
                $phoneNumber = $phoneUtil->parse($value, $defaultRegion);
            } catch (NumberParseException $e) {
                $APPLICATION->ThrowException($this->errorMessage);
                return false;
            }

            $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);

            if ($phoneUtil->isValidNumber($phoneNumber) === false) {
                $APPLICATION->ThrowException($this->errorMessage);
                return false;
            }

            return true;
        }

        return true;
    }

}