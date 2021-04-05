<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use Local\Bundles\BitrixWebformBundle\Services\Exceptions\WebFormValidateErrorException;

/**
 * Class CFormValidatorLaravel
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators
 *
 * @since 07.02.2021
 */
class CFormValidatorLaravel extends AbstractCustomBitrixWebformValidator
{
    /**
     * @var string $name Код валидатора.
     */
    private $name;

    /**
     * @var string $description Описание валидатора.
     */
    private $description;

    /**
     * @var string $rules Правила валидации. @see https://laravel.com/docs/6.x/validation#available-validation-rules
     */
    private $rules;

    /**
     * @var array $typesFields Типы полей, к которым применяется валидатор.
     */
    private $typesFields;

    /**
     * @var string $errorMessage Сообщение об ошибке, если валидация обломалась.
     */
    private $errorMessage;

    /**
     * CFormValidatorLaravel constructor.
     *
     * @param string $name         Код валидатора.
     * @param string $description  Описание валидатора.
     * @param string $rules        Правила валидации. @see https://laravel.com/docs/6.x/validation#available-validation-rules.
     * @param string $errorMessage Сообщение об ошибке, если валидация обломалась.
     * @param array  $typesFields  Типы полей, к которым применяется валидатор.
     */
    public function __construct(
        string $name,
        string $description,
        string $rules,
        string $errorMessage,
        array $typesFields
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->errorMessage = $errorMessage;
        $this->rules = $rules;
        $this->typesFields = $typesFields;
    }

    /**
     * @inheritDoc
     */
    public function GetDescription() : array
    {
        return [
            'NAME' => $this->name, // validator string ID
            'DESCRIPTION' => $this->description, // validator description
            'TYPES' => $this->typesFields, //  list of types validator can be applied.
            'HANDLER' => [$this, 'DoValidate'] // main validation method
        ];
    }

    /**
     * @inheritDoc
     */
    public function DoValidate($arParams, $arQuestion, $arAnswers, $arValues): bool
    {
        global $APPLICATION;

        foreach ($arValues as $value) {
            try {
                $this->validateAttribute($value, $this->rules);
            } catch (WebFormValidateErrorException $e) {
                $APPLICATION->ThrowException($this->errorMessage);
                return false;
            }
        }

        return true;
    }

    /**
     * Валидирует отдельный атрибут.
     *
     * @param mixed  $value Значение.
     * @param string $rule  Правила валидации.
     *
     * @return boolean
     * @throws WebFormValidateErrorException Ошибки валидации.
     */
    private function validateAttribute($value, string $rule): bool
    {
        $validator = new Validator(
            new Translator(new ArrayLoader(), 'en_US'),
            ['key' => $value],
            ['key' => $rule],
            [$this->errorMessage]
        );

        if ($validator->fails()) {
            throw new WebFormValidateErrorException(implode(', ', $validator->errors()->all()));
        }

        return true;
    }
}
