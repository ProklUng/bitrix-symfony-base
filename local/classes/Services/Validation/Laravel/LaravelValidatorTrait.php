<?php

namespace Local\Services\Validation\Laravel;

use Illuminate\Validation\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use InvalidArgumentException;

/**
 * Trait LaravelValidatorTrait
 * @package Local\Services\Validation
 */
trait LaravelValidatorTrait
{
    /**
     * Валидирует переданные данные.
     *
     * @param array      $data     Данные
     * @param array|null $rules    Правила валидации.
     * @param array|null $messages Сообщение.
     *
     * @return boolean
     * @throws InvalidArgumentException
     *
     * @since 08.09.2020 Change doc block.
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
            throw new InvalidArgumentException(implode(', ', $validator->errors()->all()));
        }

        return true;
    }

    /**
     * Валидирует отдельный атрибут.
     *
     * @param string     $attribute Аттрибут.
     * @param mixed      $value     Валидируемое значение.
     * @param array|null $rules     Правила валидации.
     *
     * @return boolean
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
            throw new InvalidArgumentException(implode(', ', $validator->errors()->all()));
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
     * @param array|null $messages
     *
     * @return  array
     */
    protected function getValidationMessages(array $messages = null): array
    {
        return $messages ?? (property_exists($this, 'messages')
                ? $this->messages : DefaultValidationMessages::getItems());
    }
}
