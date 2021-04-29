<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions;

use Prokl\BaseException\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class InvalidAjaxCallException
 * Исключения классов пространства имен Events.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sine 09.09.2020
 */
class InvalidAjaxCallException extends BaseException implements RequestExceptionInterface
{

}
