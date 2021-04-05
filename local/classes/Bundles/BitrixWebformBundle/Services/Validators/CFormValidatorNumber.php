<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators;

/**
 * Class CFormValidatorNumber
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators
 *
 * @since 06.02.2021
 */
class CFormValidatorNumber extends AbstractCustomBitrixWebformValidator
{
    /**
     * @var string $errorMessage
     */
    private $errorMessage = '#FIELD_NAME#: допустимы только числовые значения';

    /**
     * @inheritDoc
     */
    public function GetDescription() : array
    {
        return [
            "NAME" => "number", // validator string ID
            "DESCRIPTION" => 'Валидация на число', // validator description
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
            // empty string is not a number but we won't return error - crossing with "required" mark
            if ($value !== "" && (($value !==0  && (int)$value == 0) || strval((int)$value) !== strval($value)))
            {
                $APPLICATION->ThrowException($this->errorMessage);
                return false;
            }
        }

        return true;
    }

}