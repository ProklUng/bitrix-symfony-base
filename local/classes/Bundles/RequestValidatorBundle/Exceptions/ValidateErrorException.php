<?php

namespace Local\Bundles\RequestValidatorBundle\Exceptions;

use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class ValidateErrorException
 * @package Local\Bundles\RequestValidatorBundle\Exceptions
 *
 * @sinсe 05.04.2021
 */
class ValidateErrorException extends BaseException implements RequestExceptionInterface
{

}
