<?php

namespace Local\Bundles\FacadeBundle\Tests;

use Exception;
use InvalidArgumentException;
use Local\Bundles\FacadeBundle\DependencyInjection\CompilerPass\AddFacadePass;
use Local\Bundles\FacadeBundle\Services\AbstractFacade;
use Local\Bundles\FacadeBundle\Tests\Fixture\Facades\Foo;
use Local\Bundles\FacadeBundle\Tests\Fixture\Facades\InvalidFacade;
use Local\Bundles\FacadeBundle\Tests\Fixture\Services\FooService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class AddFacadePassTest
 * @package Local\Bundles\FacadeBundle\Tests
 *
 * @since 15.04.2021
 */
class AddFacadePassTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testProsesFacade() : void
    {
        $container = new ContainerBuilder();

        $container->register(FooService::class);
        $container->register(Foo::class)->addTag('laravel.facade');

        $addFacadePass = new AddFacadePass();
        $addFacadePass->process($container);

        $container->compile();

        $this->assertInstanceOf(ServiceLocator::class, $container->get('laravel.facade.container'));
        $this->assertTrue($container->get('laravel.facade.container')->has(Foo::class));
    }

    /**
     * @return void
     */
    public function testProsesInvalidFacade() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The service "Local\Bundles\FacadeBundle\Tests\Fixture\Facades\InvalidFacade" must extend AbstractFacade.'
        );

        $container = new ContainerBuilder();

        $container->register(InvalidFacade::class)->addTag('laravel.facade');

        $addFacadePass = new AddFacadePass();
        $addFacadePass->process($container);

        $container->compile();
    }
}
