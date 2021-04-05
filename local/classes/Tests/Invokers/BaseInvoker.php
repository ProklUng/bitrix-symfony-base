<?php

namespace Local\Tests\Invokers;

use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class BaseInvoker
 * @package Local\Tests\Invokers
 */
abstract class BaseInvoker
{

    abstract public function execute();

    /**
     * Выполнить метод.
     *
     * @param object     $object Объект.
     * @param string     $method Метод.
     * @param array|null $params Параметры.
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected static function invokeMethod($object, $method, array $params = null)
    {
        $method = new ReflectionMethod($object, $method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $params ? $params : []);
    }

    /**
     * Установить свойство объекта.
     *
     * @param object     $object   Объект.
     * @param string     $property Свойство.
     * @param mixed|null $value    Значение.
     *
     * @throws ReflectionException
     */
    protected static function setObjectPropertyValue($object, $property, $value = null)
    {
        $property = new ReflectionProperty($object, $property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Получить свойство объекта.
     *
     * @param object $object   Объект.
     * @param string $property Свойство.
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected static function getObjectPropertyValue($object, $property)
    {
        $property = new ReflectionProperty($object, $property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
