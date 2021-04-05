<?php

namespace Local\SymfonyTools\Framework\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class BaseException
 * Мои базовые исключения.
 * @package Local\SymfonyTools\Framework\Exceptions
 * @codeCoverageIgnore
 *
 * @since 05.09.2020
 */
class BaseException extends Exception implements ExceptionInterface, RequestExceptionInterface
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
