<?php

namespace Local\Services\Validation\Traits;

use Illuminate\Validation\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Local\Services\Validation\Exceptions\ValidateErrorException;
use Local\Util\Dictionaries\DefaultValidationMessages as DefaultMessages;

/**
 * Trait Validatable
 * @package Local\Services\Validation\Traits
 */
trait Validatable
{
    /**
     * Валидирует переданные данные
     *
     * @param array      $data     Данные, подлежащие валидации.
     * @param array|null $rules    Правила валидации.
     * @param array|null $messages Сообщение.
     *
     * @return boolean
     * @throws ValidateErrorException Ошибки валидации.
     */
    public function validate(array $data, array $rules = null, array $messages = null): bool
    {
        $rules = $rules ?? (property_exists($this, 'rules') ? $this->rules : []);

        $validator = new Validator(
            new Translator(new ArrayLoader(), $this->getValidationLocale()),
            $data,
            $rules,
            $this->getValidationMessages($messages)
        );

        if ($validator->fails()) {
            throw new ValidateErrorException(implode(', ', $validator->errors()->all()));
        }

        return true;
    }

    /**
     * Валидирует отдельный атрибут.
     *
     * @param string     $attribute Атрибут.
     * @param mixed      $value     Значение.
     * @param array|null $rules     Правила валидации.
     *
     * @return boolean
     * @throws ValidateErrorException Ошибки валидации.
     */
    public function validateAttribute(string $attribute, $value, array $rules = null): bool
    {
        if (empty($rules)) {
            if (property_exists($this, 'rules') && !empty($this->rules[$attribute])) {
                $rules = [$attribute => $this->rules[$attribute]];
            } else {
                $rules = [$attribute => []];
            }
        }

        $validator = new Validator(
            new Translator(new ArrayLoader(), $this->getValidationLocale()),
            [$attribute => $value],
            $rules,
            $this->getValidationMessages()
        );

        if ($validator->fails()) {
            throw new ValidateErrorException(implode(', ', $validator->errors()->all()));
        }

        return true;
    }

    /**
     * getValidationLocale.
     *
     * @return  string
     */
    protected function getValidationLocale(): string
    {
        return property_exists($this, 'locale') ? $this->locale : 'en_US';
    }

    /**
     * getValidationMessages.
     *
     * @param array|null $messages Сообщение.
     *
     * @return  array
     */
    protected function getValidationMessages(array $messages = null): array
    {
        return $messages ?? (property_exists($this, 'messages') ? $this->messages : DefaultMessages::getItems());
    }
}
