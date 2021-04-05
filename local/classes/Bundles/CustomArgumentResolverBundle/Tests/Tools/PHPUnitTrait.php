<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Tools;

use Exception;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Container;

/**
 * Trait PHPUnitTrait
 * Утилиты для тестирования.
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Tools
 */
trait PHPUnitTrait
{
    /**
     * Мок интерфейса.
     *
     * @param string $className  Имя класса.
     * @param string $method     Метод.
     * @param mixed $returnValue Возвращаемое значение.
     *
     * @return MockObject
     */
    public function mockInterface(
        string $className,
        string $method,
        $returnValue
    ): MockObject {
        /** @var MockObject $mockInterface */
        $mockInterface = $this->createMock($className);
        $mockInterface->method($method)
            ->willReturn($returnValue);

        return $mockInterface;
    }

    /**
     * Мок интерфейса, ничего не возвращающего.
     *
     * @param string $className Имя класса.
     * @param string $method    Метод.
     *
     * @return MockObject
     */
    public function mockInterfaceVoid(
        string $className,
        string $method
    ): MockObject {
        /** @var MockObject $mockInterface */
        $mockInterface = $this->createMock($className);
        $mockInterface->method($method);

        return $mockInterface;
    }

    /**
     * Мок абстрактного класса.
     *
     * @param string $classname   Класс.
     * @param array  $arArguments Аргументы конструктора.
     *
     * @return mixed
     */
    public function mockAbstractClass(string $classname, array $arArguments = [])
    {
        return $this
            ->getMockBuilder($classname)
            ->setConstructorArgs($arArguments)
            ->getMockForAbstractClass();
    }

    /**
     * Проверка структуры массивы.
     *
     * @param $keys
     * @param $array
     *
     * @throws JsonException
     */
    public function assertArrayStructure($keys, $array) : void
    {
        $arrayKeys = array_keys($array);

        $missing = array_diff($keys, $arrayKeys);
        $unexpected = array_diff($arrayKeys, $keys);

        $message = 'Keys are wrong.';
        if ($missing !== []) {
            $message .= ' Missing: ' . implode(', ', $missing) . '.';
        }

        if ($unexpected !== []) {
            $message .= ' Unexpected: ' . implode(', ', $unexpected) . '.';
        }

        $message .= "\n" .json_encode($array, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);

        $this->assertTrue($missing === [] && $unexpected === [], $message);
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
            $this->testObject,
            $prop
        );

        $this->assertSame(
            $expected,
            $result,
            $message ?: $prop.' не тот, что ожидался. '
        );
    }

    /**
     * assertEqualsCanonicalizing защищенного свойства.
     *
     * @param string $prop     Название переменной.
     * @param mixed  $expected Ожидаемое значение.
     * @param string $message  Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertEqualsArrayProtectedProp(
        string $prop,
        $expected,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        self::assertEqualsCanonicalizing(
            $expected,
            $result,
            $message ?: $prop.' не тот, что ожидался. '
        );
    }

    /**
     * assertIsNumeric защищенного свойства.
     *
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertIsNumericProtectedProp(
        string $prop,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        self::assertIsNumeric(
            $result,
            $message ?: $prop.' не тот, что ожидался. '
        );
    }


    /**
     * assertIsArray защищенного свойства.
     *
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertIsArrayProtectedProp(
        string $prop,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        self::assertIsArray(
            $result,
            $message ?: $prop.' не тот, что ожидался. '
        );
    }

    /**
     * assertNotSame защищенного свойства.
     *
     * @param string $prop     Название переменной.
     * @param mixed  $expected Ожидаемое значение.
     * @param string $message  Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertNotSameProtectedProp(
        string $prop,
        $expected,
        string $message = ''
    ): void {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        self::assertNotSame(
            $expected,
            $result,
            $message ?: $prop.' не тот, что ожидался.'
        );
    }

    /**
     * assertEmpty защищенного свойства.
     *
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertEmptyProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        self::assertEmpty(
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
            $this->testObject,
            $prop
        );

        self::assertEmpty(
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
            $this->testObject,
            $prop
        );

        self::assertNotEmpty(
            $result[$key],
            $message
        );
    }

    /**
     * assertNotEmpty защищенного свойства.
     *
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertNotEmptyProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        self::assertNotEmpty(
            $result,
            $message
        );
    }

    /**
     * assertNull защищенного свойства.
     *
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertNullProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        self::assertNull(
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
            $this->testObject,
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
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertFalseProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
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
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertNotNullProtectedProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        $this->assertNotNull(
            $result,
            $message
        );
    }


    /**
     * assertNotNull статического свойства.
     *
     * @param string $prop    Название переменной.
     * @param string $message Сообщение об ошибке.
     *
     * @throws ReflectionException
     */
    protected function assertNotNullStaticProp(string $prop, string $message = ''): void
    {
        $result = PHPUnitUtils::getStaticProperty(
            get_class($this->testObject),
            $prop,
        );

        $this->assertNotNull(
            $result,
            $message
        );
    }

    /**
     * Проверка инжекции.
     *
     * @param string $prop     Название свойства.
     * @param string $expected Ожидаемый класс.
     * @param string $message  Сообщение.
     *
     * @throws ReflectionException
     */
    protected function assertInjectionProtectedProp(
        string $prop,
        string $expected,
        string $message = ''
    ) : void {
        $testObject = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        $this->assertInstanceOf(
            $expected,
            $testObject,
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
        $testObject = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        $this->assertNotInstanceOf(
            $expected,
            $testObject,
            'Прошла неожиданная инжекция ' . $expected
        );
    }

    /**
     * Тест сеттера.
     *
     * @param string $method Сеттер.
     * @param string $prop   Свойство
     * @param mixed  $value  Значение.
     *
     * @throws ReflectionException
     */
    protected function checkSetter(string $method, string $prop, $value): void
    {
        PHPUnitUtils::callMethod(
            $this->testObject,
            $method,
            [
                $value
            ]
        );

        $settedValue = PHPUnitUtils::getProtectedProperty(
            $this->testObject,
            $prop
        );

        $this->assertSame(
            $value,
            $settedValue,
            'Сеттер ' . $method . ' не сработал.'
        );
    }

    /**
     * Очистить приватное статическое свойства.
     *
     * @param mixed  $className Название класса.
     * @param string $property  Свойство.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function clearStaticProperty($className, $property): void
    {
        $reflection = new ReflectionProperty($className, $property);
        $reflection->setAccessible(true);

        $reflection->setValue(null, null);
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
     * Рекурсивный поиск в массиве.
     *
     * @param array  $array  Массив.
     * @param string $needle Что ищем.
     *
     * @return boolean true: нашли.
     */
    protected function searchArrayRecursive(array $array, string $needle) : bool
    {
        $result = false;

        array_walk_recursive($array, static function ($value) use ($needle, &$result) {
            if (!is_array($value) && $value === $needle) {
                $result = true;
            }
        });

        return $result;
    }
}
