<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Tools;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BaseTestCase
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Tools
 *
 * @since 05.12.2020
 */
class BaseTestCase extends TestCase
{
    use ExceptionAsserts;
    use PHPUnitTrait;

    /**
     * @var mixed $testObject Тестируемый объект.
     */
    protected $testObject;

    /**
     * @var Generator | null $faker
     */
    protected $faker;

    /**
     * @var ContainerInterface $testContainer Тестовый контейнер.
     */
    protected static $testContainer;

    protected function setUp(): void
    {
        // Инициализация тестового контейнера.
        static::$testContainer = container()->get('custom_arguments_resolvers.test.service_container')
            ?: container();

        Mockery::resetContainer();
        parent::setUp();

        $this->faker = Factory::create();
    }

    protected function tearDown(): void
    {
        // Сбросить тестовый контейнер.
        static::$testContainer->reset();

        parent::tearDown();

        Mockery::close();

        $this->testObject = null;
    }
}
