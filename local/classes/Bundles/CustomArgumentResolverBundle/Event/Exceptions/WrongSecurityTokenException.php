<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions;

use Local\Bundles\CustomArgumentResolverBundle\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class WrongSecurityTokenException
 * Исключения классов пространства имен Events.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sine 09.09.2020
 */
class WrongSecurityTokenException extends BaseException implements RequestExceptionInterface
{

}
