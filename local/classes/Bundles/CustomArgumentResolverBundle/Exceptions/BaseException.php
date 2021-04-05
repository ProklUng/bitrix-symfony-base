<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Exceptions;

use Exception;

/**
 * Class BaseException
 * Базовые исключения.
 * @package Local\Bundles\CustomArgumentResolverBundle\Exceptions
 * @codeCoverageIgnore
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
