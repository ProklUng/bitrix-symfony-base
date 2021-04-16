<?php

namespace Local\Bundles\FacadeBundle\Tests;

use Local\Bundles\FacadeBundle\DependencyInjection\FacadeExtension;
use Local\Bundles\FacadeBundle\Services\AbstractFacade;
use Local\Bundles\FacadeBundle\Tests\Fixture\Facades\Foo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class FacadeBundleTest
 * @package Local\Bundles\FacadeBundle\Tests
 *
 * @since 15.04.2021
 */
class FacadeBundleTest extends TestCase
{
    /**
     * @var ContainerInterface $testContainer Тестовый контейнер.
     */
    protected static $testContainer;

    protected function setUp(): void
    {
        // Инициализация тестового контейнера.
        static::$testContainer = container();

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testBundle() : void
    {
        $this->assertTrue(static::$testContainer->has('laravel.facade.container'));
        $this->assertInstanceOf(ServiceLocator::class, static::$testContainer->get('laravel.facade.container'));
    }

    /**
     * @return void
     */
    public function testRegisterAutoconfigure() : void
    {
        $container = new ContainerBuilder();
        $container->register(Foo::class);

        $extension = new FacadeExtension();
        $extension->load([], $container);

        $this->assertArrayHasKey(
            AbstractFacade::class,
            $container->getAutoconfiguredInstanceof()
        );

        $this->assertArrayHasKey('laravel.facade', $container->getAutoconfiguredInstanceof()[AbstractFacade::class]->getTags());
    }
}
