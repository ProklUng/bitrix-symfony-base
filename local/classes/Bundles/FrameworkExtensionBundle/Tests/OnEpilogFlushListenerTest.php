<?php

namespace Local\Bundles\FrameworkExtensionBundle\Tests;

use Local\Bundles\FrameworkExtensionBundle\Services\Bitrix\OnEpilogFlushListener;
use Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents\DelayedEventDispatcher;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @since 13.04.2021
 */
class OnEpilogFlushListenerTest extends TestCase
{
    /**
     * @var mixed
     */
    private $dispatcher;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Mockery::resetContainer();

        $mock = Mockery::mock(DelayedEventDispatcher::class);

        $this->dispatcher = $mock->shouldReceive('flush')->once()->getMock();

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $listener = new OnEpilogFlushListener($this->dispatcher);
        $listener->handle();

        $this->assertTrue(true);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
