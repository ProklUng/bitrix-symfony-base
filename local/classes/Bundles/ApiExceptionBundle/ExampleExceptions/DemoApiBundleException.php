<?php

namespace Local\Bundles\ApiExceptionBundle\ExampleExceptions;

use Local\Bundles\ApiExceptionBundle\Exception\HttpException;

/**
 * Class DemoApiBundleException
 * Простой exception, описанный в конфигурации бандла.
 * throw new DemoApiBundleException('Ошибка')
 * @package Local\Bundles\ApiExceptionBundle\ExampleExceptions
 *
 * @since 25.10.2020
 */
class DemoApiBundleException extends HttpException
{

}
