<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

/**
 * Class CFormValidatorEmail
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators
 *
 * @since 06.02.2021
 */
class CFormValidatorEmail extends AbstractCustomBitrixWebformValidator
{
    /**
     * @var EmailValidator $emailValidator
     */
    private $emailValidator;

    /**
     * @var string $errorMessage
     */
    private $errorMessage = '#FIELD_NAME#: невалидный адрес электронной почты';

    /**
     * CFormValidatorEmail constructor.
     *
     * @param EmailValidator $emailValidator
     */
    public function __construct(EmailValidator $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    /**
     * @inheritDoc
     */
    public function GetDescription() : array
    {
        return [
            "NAME" => "custom_email", // validator string ID
            "DESCRIPTION" => 'Валидация email', // validator description
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
            if ($value && !$this->emailValidator->isValid($value, new RFCValidation()))
            {
                $APPLICATION->ThrowException($this->errorMessage);
                return false;
            }
        }

        return true;
    }

}