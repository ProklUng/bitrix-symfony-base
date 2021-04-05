<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators;

/**
 * Class CFormValidatorTextLen
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators
 *
 * @since 06.02.2021
 */
class CFormValidatorTextLen extends AbstractCustomBitrixWebformValidator
{
    /**
     * @var string $minText
     */
    private $minText = 'Минимальная длина';

    /**
     * @var string $maxText
     */
    private $maxText = 'Максимальная длина';

    /**
     * @var string $minTextErrorMessage
     */
    private $minTextErrorMessage = 'Ошибка по минимальной длине.';

    /**
     * @var string $maxTextErrorMessage
     */
    private $maxTextErrorMessage = 'Ошибка по максимальной длине.';

    /**
     * @inheritDoc
     */
    public function GetDescription() : array
    {
        return [
            "NAME" => "text_len", // unique validator string ID
            "DESCRIPTION" => "Валидация по максимальной и минимальной длине текстового поля", // validator description
            "TYPES" => ["text", "textarea", "password", "email", "url"], //  list of types validator can be applied.
            "SETTINGS" => [$this, "GetSettings"], // method returning array of validator settings, optional
            "CONVERT_TO_DB" => [$this, "ToDB"], // method, processing validator settings to string to put to db, optional
            "CONVERT_FROM_DB" => [$this, "FromDB"], // method, processing validator settings from string from db, optional
            "HANDLER" => [$this, "DoValidate"]
            // main validation method
        ];
    }

    public function GetSettings() : array
    {
        return array(
            "LENGTH_FROM" => array(
                "TITLE" => $this->minText,
                "TYPE" => "TEXT",
                "DEFAULT" => "0",
            ),

            "LENGTH_TO" => array(
                "TITLE" => $this->maxText,
                "TYPE" => "TEXT",
                "DEFAULT" => "100",
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function ToDB($arParams)
    {
        $arParams["LENGTH_FROM"] = (int)$arParams["LENGTH_FROM"];
        $arParams["LENGTH_TO"] = (int)$arParams["LENGTH_TO"];

        if ($arParams["LENGTH_FROM"] > $arParams["LENGTH_TO"])
        {
            $tmp = $arParams["LENGTH_FROM"];
            $arParams["LENGTH_FROM"] = $arParams["LENGTH_TO"];
            $arParams["LENGTH_TO"] = $tmp;
        }

        return serialize($arParams);
    }

    /**
     * @inheritDoc
     */
    public function DoValidate($arParams, $arQuestion, $arAnswers, $arValues) : bool
    {
        global $APPLICATION;

        foreach ($arValues as $value)
        {
            // check minimum length
            if (strlen($value) < $arParams["LENGTH_FROM"])
            {
                $APPLICATION->ThrowException(
                    $this->minTextErrorMessage
                );
                return false;
            }

            // check maximum length
            if (strlen($value) > $arParams["LENGTH_TO"])
            {
                $APPLICATION->ThrowException(
                    $this->maxTextErrorMessage
                );
                return false;
            }
        }

        return true;
    }
}