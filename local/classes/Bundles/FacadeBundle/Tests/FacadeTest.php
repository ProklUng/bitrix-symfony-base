<?php

namespace Local\Bundles\FacadeBundle\Tests;

use Exception;
use Local\Bundles\FacadeBundle\DependencyInjection\CompilerPass\AddFacadePass;
use Local\Bundles\FacadeBundle\Services\AbstractFacade;
use Local\Bundles\FacadeBundle\Tests\Fixture\Facades\Foo;
use Local\Bundles\FacadeBundle\Tests\Fixture\Services\FooService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class FacadTest
 * @package Local\Bundles\FacadeBundle\Tests
 *
 * @since 15.04.2021
 */
class FacadeTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Mockery::resetContainer();
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Foo::clearResolvedInstance();
        Mockery::close();
    }

    /**
     * @return void
     */
    public function testRegisteredFacade() : void
    {
        $container = $this->createMock(ServiceLocator::class);
        $container
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturnMap([
                [Foo::class, true],
            ])
        ;

        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(new FooService())
        ;

        AbstractFacade::setFacadeContainer($container);

        $fooService = new FooService();
        $this->assertSame($fooService->sayHello(), Foo::sayHello());
        $this->assertSame($fooService->callWithArgs('foo', 'bar'), Foo::callWithArgs('foo', 'bar'));
    }

    /**
     * @return void
     */
    public function testNotRegisteredFacade() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('"%s" facade has not been register.', Foo::class));

        $container = $this->createMock(ServiceLocator::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->willReturnMap([])
        ;

        AbstractFacade::setFacadeContainer($container);

        $fooService = new FooService();
        Foo::sayHello();
    }

    /**
     * Mocking.
     * @noinspection PhpUndefinedMethodInspection
     *
     * @return void
     * @throws Exception
     */
    public function testMocking() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        Foo::shouldReceive('sayHello')->andReturn('mocked');
        $result = Foo::sayHello();


        $this->assertSame(
            'mocked',
            $result,
            'Мок не сработал.'
        );
    }

    /**
     * swapService().
     *
     * @return void
     * @throws Exception
     */
    public function testSwapService() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        $mock = Mockery::mock(FooService::class)
                        ->shouldReceive('sayHello')
                        ->andReturn('mocked')
                        ->getMock();

        Foo::swapService($mock);

        $result = Foo::sayHello();

        $this->assertSame('mocked', $result);
    }

    /**
     * getFacadeApplication().
     *
     * @return void
     * @throws Exception
     */
    public function testGetFacadeApplication() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        $result = Foo::getFacadeApplication();
        $this->assertInstanceOf(ServiceLocator::class, $result);
    }

    /**
     * spy().
     *
     * @return void
     * @throws Exception
     */
    public function testSpy() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        $result = Foo::spy();

        $this->assertInstanceOf(MockInterface::class, $result);
    }

    /**
     * spy(). For mock.
     *
     * @return void
     * @throws Exception
     */
    public function testSpyForMock() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        Foo::shouldReceive('sayHello')->andReturn('mocked')->getMock();

        $result = Foo::spy();
        $this->assertNull($result);
    }

    /**
     * partialMock().
     *
     * @return void
     * @throws Exception
     */
    public function testMakePartial() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        $result = Foo::partialMock();

        $this->assertInstanceOf(MockInterface::class, $result);
    }

    /**
     * clearResolvedInstance().
     *
     * @return void
     * @throws Exception
     */
    public function testClearResolvedInstance() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        Foo::shouldReceive('sayHello')
            ->andReturn('mocked');

        Foo::clearResolvedInstance();

        $fooService = new FooService();
        $result = Foo::sayHello();

        $this->assertSame($fooService->sayHello(), $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRegisteredFacadeFunctional() : void
    {
        $container = $this->getTestContainer();

        AbstractFacade::setFacadeContainer($container);

        $result = Foo::sayHello();

        $fooService = new FooService();

        $this->assertSame($fooService->sayHello(), $result);
        $this->assertSame($fooService->callWithArgs('foo', 'bar'), Foo::callWithArgs('foo', 'bar'));
    }

    /**
     * Тестовый локатор.
     *
     * @return mixed
     * @throws Exception
     */
    private function getTestContainer()
    {
        $container = new ContainerBuilder();
        $container->setDefinition(FooService::class, new Definition(FooService::class))->setPublic(true);
        $container->setDefinition(Foo::class, new Definition(Foo::class))->addTag('laravel.facade')->setPublic(true);

        $addFacadePass = new AddFacadePass();
        $addFacadePass->process($container);

        $container->compile();

        return $container->get('laravel.facade.container');
    }
}
