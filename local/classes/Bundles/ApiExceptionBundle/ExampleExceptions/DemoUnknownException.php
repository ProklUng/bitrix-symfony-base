<?php

namespace Local\Bundles\ApiExceptionBundle\ExampleExceptions;

use Local\Bundles\ApiExceptionBundle\Exception\HttpException;
use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\FlattenErrorExceptionInterface;

/**
 * Class DemoApiBundleVariableException
 * Exception, описанный в конфигурации бандла, с массивом ошибок.
 * throw new DemoApiBundleException('error1', 'error2') // Параметры - строка!
 * @package Local\Bundles\ApiExceptionBundle\ExampleExceptions
 *
 * @since 25.10.2020
 */
class DemoUnknownException extends HttpException implements FlattenErrorExceptionInterface
{
    /**
     * @var mixed
     */
    protected $var1;

    /**
     * @var mixed
     */
    protected $var2;

    public function __construct($var1, $var2)
    {
        $this->var1 = $var1;
        $this->var2 = $var2;
        parent::__construct();
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getFlattenErrors() : array
    {
        $errors = [$this->var1, $this->var2];

        /* your algo with $var1 and $var2 to compose array */

        return $errors;
    }
}
