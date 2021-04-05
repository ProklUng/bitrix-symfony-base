<?php

namespace Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions;

use Local\Bundles\CustomArgumentResolverBundle\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class BitrixFileNotFoundException
 * @package Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions
 *
 * @sinсe 02.04.2021
 */
class BitrixFileNotFoundException extends BaseException implements RequestExceptionInterface
{

}
