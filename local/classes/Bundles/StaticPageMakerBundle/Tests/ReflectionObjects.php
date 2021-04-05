<?php

namespace Local\Bundles\StaticPageMakerBundle\Tests;

use ReflectionClass;
use ReflectionException;

/**
 * Class ReflectionObjects
 * @package Local\Bundles\StaticPageMakerBundle\Tests
 *
 * @since 25.01.2021
 */
class ReflectionObjects
{
    /**
     * Вызвать метод.
     *
     * @param mixed  $object      Объект.
     * @param string $name        Метод.
     * @param array  $arArguments Аргументы.
     *
     * @return mixed
     * @throws ReflectionException Ошибки рефлексии.
     */
    public static function callMethod($object, string $name, array $arArguments = [])
    {
        $class = new ReflectionClass($object);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arArguments);
    }
}
