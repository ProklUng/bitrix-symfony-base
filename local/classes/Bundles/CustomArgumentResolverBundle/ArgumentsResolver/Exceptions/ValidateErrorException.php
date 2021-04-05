<?php

namespace Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions;

use Local\Bundles\CustomArgumentResolverBundle\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class ValidateErrorException
 * @package Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions
 *
 * @sinсe 01.04.2021
 */
class ValidateErrorException extends BaseException implements RequestExceptionInterface
{

}
