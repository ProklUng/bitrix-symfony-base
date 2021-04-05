<?php

namespace Local\Tests;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Container;

/**
 * Trait PHPUnitTrait
 * Утилиты для тестирования.
 * @package Local\Tests
 */
trait PHPUnitTrait
{
    /**
     * Мок интерфейса.
     *
     * @param string $sClassName   Имя класса.
     * @param string $sMethod      Метод.
     * @param mixed $returnValue   Возвращаемое значение.
     * @param array  $args         Аргументы.
     *
     * @return MockObject
     */
    public function mockInterface(
        string $sClassName,
        string $sMethod,
        $returnValue
    ): MockObject {
        /** @var MockObject $mockInterface */
        $mockInterface = $this->createMock($sClassName);
        $mockInterface->method($sMethod)
            ->willReturn($returnValue);

        return $mockInterface;
    }

    /**
     * Мок интерфейса, ничего не возвращающего.
     *
     * @param string $sClassName   Имя класса.
     * @param string $sMethod      Метод.
     *
     * @return MockObject
     */
    public function mockInterfaceVoid(
        string $sClassName,
        string $sMethod
    ): MockObject {
        /** @var MockObject $mockInterface */
        $mockInterface = $this->createMock($sClassName);
        $mockInterface->method($sMethod);

        return $mockInterface;
    }

    /**
     * Мок абстрактного класса.
     *
     * @param string $sClassname  Класс.
     * @param array  $arArguments Аргументы конструктора.
     *
     * @return mixed
     */
    public function mockAbstractClass(string $sClassname, array $arArguments = [])
    {
        return $this
            ->getMockBuilder($sClassname)
            ->setConstructorArgs($arArguments)
            ->getMockForAbstractClass();
    }

    /**
     * assertSame защищенного свойства.
     *
     * @param string $prop Название переменной.
     * @param mixed $expected Ожидаемое значение.
     * @param string $message
     *
     * @throws ReflectionException
     */
    protected function assertSameProtectedProp(
        string $prop,
        $expected,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertSame(
            $expected,
            $result,
            $message ?: $prop.' не тот, что ожидался. '
        );
    }

    /**
     * assertIsNumeric защищенного свойства.
     *
     * @param string $prop Название переменной.
     * @param string $message
     *
     * @throws ReflectionException
     */
    protected function assertIsNumericProtectedProp(
        string $prop,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertIsNumeric(
            $result,
            $message ?: $prop.' не тот, что ожидался. '
        );
    }

    /**
     * assertNotSame защищенного свойства.
     *
     * @param string $prop Название переменной.
     * @param mixed $expected Ожидаемое значение.
     * @param string $message
     *
     * @throws ReflectionException
     */
    protected function assertNotSameProtectedProp(
        string $prop,
        $expected,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertNotSame(
            $expected,
            $result,
            $message ?: $prop.' не тот, что ожидался.'
        );
    }

    /**
     * Мок сервиса Symfony в контейнере.
     *
     * @param Container $container Контейнер.
     * @param string    $id        ID сервиса.
     * @param mixed     $mock      Мок.
     *
     * @return object|null
     * @throws Exception
     */
    protected function mockSymfonyService(
        Container $container,
        string $id,
        $mock
    ) {
        // Оригинальный сервис из контейнера.
        $originalService = $container->get($id);

        // Сделать возможным подмену сервиса на мок.
        $container->getDefinition($id)->setSynthetic(true);

        $container->set($id, $mock);

        // Вернуть оригинальный сервис.
        return $originalService;
    }

    /**
     * Восстановить оригинальный сервис Symfony в контейнере.
     *
     * @param Container $container Контейнер.
     * @param string    $id        Сервис.
     * @param mixed     $original  Оригинальный сервис.
     */
    protected function restoreOriginalSymfonyService(
        Container $container,
        string $id,
        $original
    ): void {
        $container->set($id, $original);
    }

    /**
     * Очистить приватное статическое свойства.
     *
     * @param mixed $sClassName Название класса.
     * @param string $property Свойство.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function clearStaticProperty($sClassName, $property): void
    {
        $reflection = new ReflectionProperty($sClassName, $property);
        $reflection->setAccessible(true);

        $reflection->setValue(null, null);
    }

    /**
     * assertEmpty защищенного свойства.
     *
     * @param string $prop Название переменной.
     *
     * @param string $message
     * @throws ReflectionException
     */
    protected function assertEmptyProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertEmpty(
            $result,
            $message
        );
    }

    /**
     * assertEmpty защищенного свойства. Ключ массива
     *
     * @param string $prop    Название переменной.
     * @param string $key     Ключ массива для проверки.
     * @param string $message Сообщение.
     *
     * @throws ReflectionException
     */
    protected function assertEmptyKeyProtectedProp(
        string $prop,
        string $key,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertEmpty(
            $result[$key],
            $message
        );
    }

    /**
     * assertEmpty защищенного свойства. Ключ массива
     *
     * @param string $prop    Название переменной.
     * @param string $key     Ключ массива для проверки.
     * @param string $message Сообщение.
     *
     * @throws ReflectionException
     */
    protected function assertNotEmptyKeyProtectedProp(
        string $prop,
        string $key,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertNotEmpty(
            $result[$key],
            $message
        );
    }

    /**
     * assertNotEmpty защищенного свойства.
     *
     * @param string $prop Название переменной.
     *
     * @param string $message
     * @throws ReflectionException
     */
    protected function assertNotEmptyProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertNotEmpty(
            $result,
            $message
        );
    }

    /**
     * assertNull защищенного свойства.
     *
     * @param string $prop Название переменной.
     *
     * @param string $message
     * @throws ReflectionException
     */
    protected function assertNullProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertNull(
            $result,
            $message
        );
    }

    /**
     * assertTrue защищенного свойства.
     *
     * @param string $prop Название переменной.
     *
     * @param string $message
     * @throws ReflectionException
     */
    protected function assertTrueProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertTrue(
            $result,
            $message
        );
    }

    /**
     * assertFalse защищенного свойства.
     *
     * @param string $prop Название переменной.
     *
     * @param string $message
     * @throws ReflectionException
     */
    protected function assertFalseProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertFalse(
            $result,
            $message
        );
    }

    /**
     * assertNotNull защищенного свойства.
     *
     * @param string $prop Название переменной.
     *
     * @param string $message
     * @throws ReflectionException
     */
    protected function assertNotNullProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertNotNull(
            $result,
            $message
        );
    }

    /**
     * Проверка инжекции.
     *
     * @param string $prop Название свойства.
     * @param string $expected Ожидаемый класс.
     * @param string $message Сообщение.
     *
     * @throws ReflectionException
     */
    protected function assertInjectionProtectedProp(
        string $prop,
        string $expected,
        string $message = ''
    ) : void {
        $obTestObject = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertInstanceOf(
            $expected,
            $obTestObject,
            $message ?: 'Инжекция ' . $expected . ' не прошла.'
        );
    }

    /**
     * Проверка инжекции. Отрицание.
     *
     * @param string $prop     Название свойства.
     * @param string $expected Ожидаемый класс.
     *
     * @throws ReflectionException
     */
    protected function assertNotInjectionProtectedProp(string $prop, string $expected) : void
    {
        $obTestObject = PHPUnitUtils::getProtectedProperty(
            $this->obTestObject,
            $prop
        );

        $this->assertNotInstanceOf(
            $expected,
            $obTestObject,
            'Прошла неожиданная инжекция ' . $expected
        );
    }
}
