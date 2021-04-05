<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class BaseException
 * @package Local\Bundles\BitrixWebformBundle\Services\Exceptions
 * @codeCoverageIgnore
 *
 * @since 05.09.2020
 */
class BaseWebformException extends Exception implements ExceptionInterface, RequestExceptionInterface
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
