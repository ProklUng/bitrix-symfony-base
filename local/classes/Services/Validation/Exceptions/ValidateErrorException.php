<?php

namespace Local\Services\Validation\Exceptions;

use Local\SymfonyTools\Framework\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class ValidateErrorException
 * @package Local\Services\Validation\Exceptions
 *
 * @since 10.09.2020
 */
class ValidateErrorException extends BaseException implements RequestExceptionInterface
{

}
