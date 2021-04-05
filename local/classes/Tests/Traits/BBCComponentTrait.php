<?php

namespace Local\Tests\Traits;

use Local\Tests\PHPUnitUtils;
use ReflectionException;

/**
 * Trait BBCComponentTrait
 * @package Local\Tests\Traits
 */
trait BBCComponentTrait
{
    /**
     * Задать arParams компонента.
     *
     * @param array $arParams
     * @throws ReflectionException
     */
    private function arParams(array $arParams = []) : void
    {
        PHPUnitUtils::setProtectedProperty(
            $this->obTestObject,
            'arParams',
            $arParams
        );
    }

    /**
     * Выполнить executeMain.
     *
     * @param mixed $mock
     *
     * @return mixed
     * @throws ReflectionException
     */
    private function runExecuteMain($mock = null)
    {
        $mock = $mock ?: $this->obTestObject;

        PHPUnitUtils::callMethod(
            $mock,
            'executeMain',
            []
        );

        return PHPUnitUtils::getProtectedProperty(
            $mock,
            'arResult'
        );
    }
}
