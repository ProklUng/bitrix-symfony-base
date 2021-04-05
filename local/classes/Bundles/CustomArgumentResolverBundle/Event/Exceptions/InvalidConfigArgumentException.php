<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions;

use Local\Bundles\CustomArgumentResolverBundle\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class InvalidConfigArgumentException
 * Исключения классов пространства имен Events.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sine 04.12.2020
 */
class InvalidConfigArgumentException extends BaseException implements RequestExceptionInterface
{

}
