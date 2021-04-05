<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Services;

use Local\Bundles\CustomArgumentResolverBundle\Service\Utils\IgnoredAutowiringControllerParamsBag;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use ReflectionException;

/**
 * Class IgnoredAutowiringControllerParamsBagTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Services
 * @coversDefaultClass IgnoredAutowiringControllerParamsBag
 *
 * @since 06.12.2020
 */
class IgnoredAutowiringControllerParamsBagTest extends BaseTestCase
{
    /**
     * @var IgnoredAutowiringControllerParamsBag $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obTestObject = new IgnoredAutowiringControllerParamsBag();
    }

    /**
     * add(). Существующие классы.
     *
     * @param object $object Объект.
     *
     * @dataProvider dataProviderClasses
     * @throws ReflectionException
     */
    public function testAdd($object) : void
    {
        $this->obTestObject->add(
            [get_class($object)]
        );

        $this->obTestObject->add(
            [get_class($object)]
        );

        $this->assertTrue(
            $this->obTestObject->isIgnoredClass(get_class($object)),
            'Класс не зафиксировался в параметрах.'
        );
    }

    /**
     * add(). Существующие классы.
     *
     * @param string $class Несуществующий класс.
     *
     * @dataProvider dataProviderNotExistClasses
     * @throws ReflectionException
     */
    public function testAddNotExistClass(string $class) : void
    {
        $this->obTestObject->add(
            [$class]
        );

        $this->willSeeException(
            ReflectionException::class,
            'not exist',
            0
        );

        $this->obTestObject->isIgnoredClass($class);
    }

    /**
     * add(). Существующие классы.
     *
     * @param object $object Объект.
     *
     * @dataProvider dataProviderClasses
     * @throws ReflectionException
     */
    public function testisIgnoredClassNotAddedClass($object) : void
    {
        $this->obTestObject->add(
            [get_class($object)]
        );

        $class = new class {};

        $this->assertFalse(
            $this->obTestObject->isIgnoredClass(get_class($class)),
            'Несуществующий класс проскочил.'
        );
    }

    /**
     * Классы для тестирования.
     *
     * @return mixed
     */
    public function dataProviderClasses() : array
    {
        return [
          [$this->getAnonymousClass()],
          [$this->getAnonymousClass()],
          [$this->getAnonymousClass()],
          [$this->getAnonymousClass()],
        ];
    }

    /**
     * Несуществующие Классы для тестирования.
     *
     * @return mixed
     */
    public function dataProviderNotExistClasses() : array
    {
        return [
            [Fake1::class],
            [Fake2::class],
            [Fake3::class],
            [Fake4::class],
        ];
    }
    /**
     * @return object
     */
    private function getAnonymousClass() {
        return new class {};
    }
}
