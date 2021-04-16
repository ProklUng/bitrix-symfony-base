<?php

namespace Local\Bundles\FrameworkExtensionBundle\Tests;

use Exception;
use Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents\Delayable;
use Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents\DelayedEventDispatcher;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * Class DelayedEventDispatcherTest
 * @package Local\Bundles\FrameworkExtensionBundle\Tests
 *
 * @see https://raw.githubusercontent.com/olvlvl/delayed-event-dispatcher/master/tests/DelayedEventDispatcherTest.php
 */
class DelayedEventDispatcherTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        parent::setUp();
    }


    /**
     * Срабатывает ли вообще.
     *
     * @return void
     */
    public function testIsWorking() : void
    {
        /**
         * @var DelayedEventDispatcher $delayedDispatcher
         */
        $delayedDispatcher = container()->get('delayed.event.dispatcher');
        $event = new class extends Event implements Delayable {
            /** @var integer $test */
            public $test = 0;
        };

        $eventName = get_class($event);
        /**
         * @var EventDispatcher $dispatcher
         */
        $dispatcher = container()->get('event_dispatcher');
        $dispatcher->addListener($eventName, function ($event) {
            $event->test = 2;
        });

        $delayedDispatcher->dispatch($event);
        $delayedDispatcher->flush();

        $this->assertSame(2, $event->test);
    }

    /**
     * @test
     */
    public function wouldRemoveFailEvent() : void
    {
        $event = new class extends Event {};

        // Выбрасывает синтаксическую ошибку. После чего проверяю
        // - очистилась ли очередь.
        $dispatcher = $this->makeRealDelayedEventDispatcher(
            false,
            null,
            null
        );

        $dispatcher->dispatch($event);
        $dispatcher->flush();

        $this->assertCount(0, $dispatcher->getQueue());
    }

    /**
     * @test
     */
    public function shouldThrowException(): void
    {
        $event = new class {
        };
        $exception = new Exception;

        $this->eventDispatcher->dispatch($event)
            ->shouldBeCalled()->willThrow($exception);

        $dispatcher = $this->makeDelayedEventDispatcher();

        $dispatcher->dispatch($event);

        try {
            $dispatcher->flush();
        } catch (Throwable $e) {
            $this->assertSame($exception, $e);
            return;
        }

        $this->fail("Expected exception");
    }

    /**
     * @test
     */
    public function shouldInvokeExceptionHandler(): void
    {
        $event = new class {
        };
        $exception = new Exception;
        $invoked = false;

        $this->eventDispatcher->dispatch($event)->shouldBeCalled()->willThrow($exception);

        $dispatcher = $this->makeDelayedEventDispatcher(
            false,
            null,
            function (
                Throwable $actualException,
                $actualEvent
            ) use (
                &$invoked,
                $event,
                $exception
            ) {
                $invoked = true;
                $this->assertSame($exception, $actualException);
                $this->assertSame($event, $actualEvent);
            }
        );

        $dispatcher->dispatch($event);
        $dispatcher->flush();

        $this->assertTrue($invoked);
    }

    /**
     * @test
     */
    public function shouldInvokeFlusher(): void
    {
        $event = new class {
        };
        $invoked = false;

        $this->eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldNotBeCalled();

        $dispatcher = $this->makeDelayedEventDispatcher(
            false,
            null,
            null,
            function ($actualEvent) use (&$invoked, $event) {
                $invoked = true;
                $this->assertSame($event, $actualEvent);
            }
        );

        $dispatcher->dispatch($event);
        $dispatcher->flush();

        $this->assertTrue($invoked);
    }

    /**
     * @test
     */
    public function shouldDispatchImmediatelyWhenDisabled(): void
    {
        $event = new class {
        };

        $this->eventDispatcher->dispatch($event)->shouldBeCalled()->willReturn($event);

        $dispatcher = $this->makeDelayedEventDispatcher(
            true
        );

        $this->assertSame($event, $dispatcher->dispatch($event));
    }

    /**
     * @test
     */
    public function shouldDelayDispatchWhenEnabled(): void
    {
        $event = new class {
        };

        $this->eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->makeDelayedEventDispatcher()->dispatch($event);
    }

    /**
     * @test
     * @dataProvider provideDispatchAccordingToStateAndArbiter
     *
     * @param boolean $disabled
     * @param boolean $decision
     * @param boolean $shouldDelay
     */
    public function shouldDispatchAccordingToStateAndArbiter(bool $disabled, bool $decision, bool $shouldDelay)
    {
        $event = new class {
        };

        if ($shouldDelay) {
            $this->eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldNotBeCalled();
        } else {
            $this->eventDispatcher->dispatch($event)->shouldBeCalled()->willReturn($event);
        }

        $dispatcher = $this->makeDelayedEventDispatcher(
            $disabled,
            function ($actualEvent) use ($decision, $event) {
                $this->assertSame($event, $actualEvent);

                return $decision;
            }
        );

        $this->assertSame($event, $dispatcher->dispatch($event));
    }

    /**
     * @return array
     */
    public function provideDispatchAccordingToStateAndArbiter(): array
    {
        return [
            [ true, true, false ],
            [ true, false, false ],
            [ false, false, false ],
            [ false, true, true ],

        ];
    }

    /**
     * @param false $disabled
     * @param callable|null $delayArbiter
     * @param callable|null $exceptionHandler
     * @param callable|null $flusher
     *
     * @return DelayedEventDispatcher
     */
    private function makeDelayedEventDispatcher(
        $disabled = false,
        callable $delayArbiter = null,
        callable $exceptionHandler = null,
        callable $flusher = null
    ): DelayedEventDispatcher {
        return new DelayedEventDispatcher(
            $this->eventDispatcher->reveal(),
            $disabled,
            $delayArbiter,
            $exceptionHandler,
            $flusher
        );
    }

    /**
     * @param false $disabled
     * @param callable|null $delayArbiter
     * @param callable|null $exceptionHandler
     * @param callable|null $flusher
     *
     * @return DelayedEventDispatcher
     */
    private function makeRealDelayedEventDispatcher(
        $disabled = false,
        callable $delayArbiter = null,
        callable $exceptionHandler = null,
        callable $flusher = null
    ): DelayedEventDispatcher {
        return new DelayedEventDispatcher(
            container()->get('event_dispatcher'),
            $disabled,
            $delayArbiter,
            $exceptionHandler,
            $flusher
        );
    }
}
