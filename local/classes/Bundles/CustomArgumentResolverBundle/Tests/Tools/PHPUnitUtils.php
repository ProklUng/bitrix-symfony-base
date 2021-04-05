<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Tools;

use Exception;
use Faker\Factory;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class PHPUnitUtils
 * Утилиты для тестирования.
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Tools
 */
class PHPUnitUtils
{
    /**
     * Мок статического метода.
     *
     * @param string   $className    Полноразмерное имя класса.
     * @param string   $method       Метод.
     * @param mixed    $willbeReturn Возвращаемое значение.
     * @param int|null $times        Количество раз.
     *
     * @return mixed
     */
    public static function mockStaticMethod(
        string $className,
        string $method,
        $willbeReturn,
        int $times = null
    ) {
        $mock = Mockery::mock('alias:'.$className);
        $mock->shouldReceive($method)
            ->andReturn($willbeReturn)
            ->times($times);

        return $mock;
    }

    /**
     * Вызвать метод.
     *
     * @param mixed  $object      Объект.
     * @param string $name        Метод.
     * @param array  $arArguments Аргументы.
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
     * @param mixed  $object   Объект.
     * @param string $property Свойство.
     *
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public static function reflectProperty($object, $property): ReflectionProperty
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);

        return $reflection_property;
    }

    /**
     * Установить значение защищенного свойства.
     *
     * @param mixed  $object   Объект.
     * @param string $property Свойство.
     * @param mixed  $value    Значение.
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
     * @param mixed  $object   Объект.
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
     * @param mixed  $className Название класса.
     * @param string $property  Свойство.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function getStaticProperty($className, $property)
    {
        $reflection = new ReflectionProperty($className, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue(null);
    }

    /**
     * Установить приватное статическое свойства.
     *
     * @param mixed  $className Название класса.
     * @param string $property  Свойство.
     * @param mixed  $value     Значение.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function setStaticProperty($className, $property, $value): void
    {
        $reflection = new ReflectionProperty($className, $property);
        $reflection->setAccessible(true);

        $reflection->setValue(null, $value);
    }

    /**
     * Мок защищенного метода.
     *
     * @param TestCase $obPhpUnit           Тест-объект.
     * @param string   $className           Название класса.
     * @param string   $method              Мокируемый метод.
     * @param mixed    $returnValue         Возвращаемое значение.
     * @param array    $arConstructorParams Параметры конструктора.
     *
     * @return MockObject
     */
    public static function mockProtectedMetod(
        TestCase $obPhpUnit,
        string $className,
        string $method,
        $returnValue,
        array $arConstructorParams = []
    ): MockObject {
        // Мок защищенного метода
        $object = $obPhpUnit->getMockBuilder($className)
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
     * @param TestCase $obUnitTest      Класс PHPUnit.
     * @param string   $className       Название класса.
     * @param array    $constructorArgs Аргументы конструктора.
     * @param string   $method          Метод.
     * @param mixed    $returnValue     Возвращаемое значение.
     *
     * @return MockObject
     */
    public static function stubProtectedMethod(
        TestCase $obUnitTest,
        string $className,
        array $constructorArgs,
        string $method,
        $returnValue
    ): MockObject {
        $stub = $obUnitTest->getMockBuilder($className)
            ->onlyMethods([$method])
            ->setConstructorArgs($constructorArgs)
            ->getMock();

        $stub->method($method)->willReturn($returnValue);

        return $stub;
    }

    /**
     * Директория пуста?
     *
     * @param string $dir
     *
     * @return boolean
     * @throws Exception
     */
    public static function isDirEmpty(string $dir): bool
    {
        $arResult = [];
        if (!$dir) {
            throw new Exception('Не задали директорию для листинга');
        }

        $arFiles = scanDir($dir);

        foreach ($arFiles as $dirItem) {
            if ($dirItem === '.' || $dirItem === '..') {
                continue;
            }

            $arResult[] = $dirItem;
        }

        return empty($arResult);
    }

    /**
     * Массивы идентичны?
     *
     * @param array $array1 Массив 1.
     * @param array $array2 Массив 2.
     *
     * @return boolean
     */
    public static function arraysSimilar(array $array1, array $array2): bool
    {
        if (count(array_diff_assoc($array1, $array2))) {
            return false;
        }

        foreach ($array1 as $k => $v) {
            if ($v !== $array2[$k]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Empty для ассоциативного массива.
     *
     * @param array $array
     *
     * @return boolean
     */
    public static function emptyAssociative(array $array): bool
    {
        foreach ($array as $value) {
            if (!empty($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * STRIPOS по одномерному массиву.
     *
     * @param array  $array  Массив.
     * @param string $needle Что ищем.
     *
     * @return boolean
     */
    public static function striposArray(array $array, string $needle): bool
    {
        foreach ($array as $value) {
            if (stripos($value, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ассоциативный массив пуст?
     *
     * @param array $array
     *
     * @return boolean
     */
    public static function assocArrayVoid(array $array): bool
    {
        foreach ($array as $value) {
            if ($value !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Фэковый путь к файлу (только для Windows).
     *
     * @return string
     */
    public static function fakerPathFile(): string
    {
        $faker = Factory::create();

        return $faker->lexify('?:\\').implode('\\', $faker->words($faker->numberBetween(0, 4)));
    }

    /**
     * Фэйковый ID поста.
     *
     * @return integer
     */
    public static function getFakeIdPost(): int
    {
        $faker = Factory::create();

        return $faker->numberBetween(1000000000, 1000000000000);
    }

    /**
     * Мок переменной, автовайренной сервисом.
     *
     * @param mixed  $object   Объект
     * @param string $variable Переменная.
     * @param mixed  $mock     Мок.
     *
     * @throws ReflectionException
     */
    public static function mockServiceVariable(
        $object,
        $variable,
        $mock
    ): void {
        self::setProtectedProperty(
            $object,
            $variable,
            $mock,
        );
    }
}
