<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Exception;

use Exception;
use Throwable;

/**
 * Class NotImplementedMethodException
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Exception
 *
 */
class NotImplementedMethodException extends Exception
{
    /**
     * NotImplementedMethodException constructor.
     *
     * @param string $methodName
     * @param string $propertyTypeClassName
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($methodName, $propertyTypeClassName, $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            'Method %s::%s() not implemented! Implement it or remove from getCallbacksMapping()',
            $propertyTypeClassName,
            $methodName
        );
        parent::__construct($message, $code, $previous);
    }

}
