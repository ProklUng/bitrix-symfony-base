<?php

namespace Local\Services\Validation\Interfaces;

/**
 * Interface ValidatorInterface
 * @package Local\Services\Validation\Interfaces
 *
 * @since 08.09.2020 Change name.
 */
interface ValidatorInterface
{
    /**
     * Валидирует переданные данные.
     *
     * @param array $data
     * @param array|null $rules Default: null
     * @param array|null $messages
     *
     * @return bool
     */
    public function validate(array $data, array $rules = null, array $messages = null): bool;

    /**
     * Валидирует отдельный атрибут.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array|null $rules Default: null
     *
     * @return bool
     */
    public function validateAttribute(string $attribute, $value, array $rules = null): bool;
}
