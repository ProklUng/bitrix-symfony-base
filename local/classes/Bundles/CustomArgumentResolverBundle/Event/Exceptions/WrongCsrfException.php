<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions;

use Local\Bundles\CustomArgumentResolverBundle\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class WrongCsrfException
 * Исключения классов пространства имен Events.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sinсe 05.09.2020
 * @since 10.09.2020 Implement RequestExceptionInterface.
 */
class WrongCsrfException extends BaseException implements RequestExceptionInterface
{

}
