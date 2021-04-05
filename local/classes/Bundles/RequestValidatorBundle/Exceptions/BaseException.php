<?php

namespace Local\Bundles\RequestValidatorBundle\Exceptions;

use Exception;

/**
 * Class BaseException
 * Базовые исключения.
 * @package Local\Bundles\RequestValidatorBundle\Exceptions
 */
class BaseException extends Exception implements ExceptionInterface
{
    /**
     * Ошибку в строку.
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
