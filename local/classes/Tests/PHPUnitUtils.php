<?php

namespace Local\Tests;

use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class PHPUnitUtils
 * @package Local\Tests
 */
class PHPUnitUtils
{
    /**
     * Мок статического метода.
     *
     * @param string   $sClassName   Полноразмерное имя класса.
     * @param string   $method       Метод.
     * @param mixed    $willbeReturn Возвращаемое значение.
     * @param int|null $times        Количество раз.
     *
     * @return array|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    public static function mockStaticMethod(
        string $sClassName,
        string $method,
        $willbeReturn,
        int $times = null
    ) {
        $mock = Mockery::mock('alias:'. $sClassName);
        $mock->shouldReceive($method)
            ->andReturn($willbeReturn)
            ->times($times);

        return $mock;
    }

    /**
     * Вызвать метод.
     *
     * @param mixed $object Объект.
     * @param string $name Метод.
     * @param array $arArguments Аргументы.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function callMethod($object, string $name, array $arArguments = [])
    {
        $class = new ReflectionClass($object);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arArguments);
    }

    /**
     * Доступ к приватному свойству.
     *
     * @param mixed $object Объект.
     * @param string $sPropertyName Свойство.
     *
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public static function reflectProperty($object, $sPropertyName): ReflectionProperty
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($sPropertyName);
        $reflection_property->setAccessible(true);

        return $reflection_property;
    }

    /**
     * Установить значение защищенного свойства.
     *
     * @param mixed $object Объект.
     * @param string $property Свойство.
     * @param mixed $value Значение.
     *
     * @throws ReflectionException
     */
    public static function setProtectedProperty($object, string $property, $value): void
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

    /**
     * Получить значение защищенного свойства.
     *
     * @param mixed $object Объект.
     * @param string $property Свойство.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function getProtectedProperty($object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);

        return $reflection_property->getValue($object);
    }

    /**
     * Получить приватное статическое свойства.
     *
     * @param mixed $sClassName Название класса.
     * @param string $property Свойство.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function getStaticProperty($sClassName, $property)
    {
        $reflection = new ReflectionProperty($sClassName, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue(null);
    }

    /**
     * Установить приватное статическое свойства.
     *
     * @param mixed $sClassName Название класса.
     * @param string $property Свойство.
     * @param mixed $value Значение.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function setStaticProperty($sClassName, $property, $value): void
    {
        $reflection = new ReflectionProperty($sClassName, $property);
        $reflection->setAccessible(true);

        $reflection->setValue(null, $value);
    }

    /**
     * Мок защищенного метода.
     *
     * @param TestCase $obPhpUnit Тест-объект.
     * @param string $sClassName Название класса.
     * @param string $method Мокируемый метод.
     * @param mixed $returnValue Возвращаемое значение.
     * @param array $arConstructorParams Параметры конструктора.
     *
     * @return MockObject
     */
    public static function mockProtectedMetod(
        TestCase $obPhpUnit,
        string $sClassName,
        string $method,
        $returnValue,
        array $arConstructorParams = []
    ): MockObject {
        // Мок защищенного метода
        $object = $obPhpUnit->getMockBuilder($sClassName)
            ->onlyMethods([$method]);

        if (!empty($arConstructorParams)) {
            $object = $object->setConstructorArgs($arConstructorParams);
        } else {
            $object = $object->disableOriginalConstructor();
        }

        $object = $object->getMock();
        // Если null, то моканый метод ничего не возвращает.
        if ($returnValue !== null) {
            $object->method($method)
                ->willReturn($returnValue);
        }

        return $object;
    }

    /**
     * Заглушка защищенного метода с аргументами и возвращаемым значением.
     *
     * @param TestCase $obUnitTest Класс PHPUnit.
     * @param string $sClassName Название класса.
     * @param array $constructorArgs Аргументы конструктора.
     * @param string $method Метод.
     * @param mixed $returnValue Возвращаемое значение.
     *
     * @return MockObject
     */
    public static function stubProtectedMethod(
        TestCase $obUnitTest,
        string $sClassName,
        array $constructorArgs,
        string $method,
        $returnValue
    ): MockObject {
        $stub = $obUnitTest->getMockBuilder($sClassName)
            ->onlyMethods([$method])
            ->setConstructorArgs($constructorArgs)
            ->getMock();

        $stub->method($method)->willReturn($returnValue);

        return $stub;
    }
}
