<?php

namespace Local\Bundles\RequestValidatorBundle\Traits;

use Local\Bundles\RequestValidatorBundle\Exceptions\ValidateErrorException;
use Local\Bundles\RequestValidatorBundle\Validator\RequestValidator;

/**
 * Trait ExceptionableValidationError
 * @package Local\Bundles\RequestValidatorBundle\Traits
 *
 * @since 05.04.2021
 */
trait ExceptionableValidationError
{
    /**
     * Выбросить исключение, если присутствуют ошибки валидации.
     *
     * @param RequestValidator $requestValidator Валидированный Request.
     *
     * @return void
     * @throws ValidateErrorException Ошибки валидации.
     */
    private function throwValidationErrorIfNeed(RequestValidator $requestValidator) : void
    {
        $errors = $requestValidator->getErrors();
        if ($errors->count() !== 0) {
            foreach ($errors as $violation) {
                $result[] = $violation->getMessage();
            }

            throw new ValidateErrorException(
                json_encode($result)
            );
        }
    }
}
