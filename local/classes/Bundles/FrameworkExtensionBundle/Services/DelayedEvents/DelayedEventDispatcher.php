<?php

namespace Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * Class DelayedEventDispatcher
 * @package Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents
 *
 * @see https://github.com/olvlvl/delayed-event-dispatcher/blob/master/lib/DelayedEventDispatcher.php
 * @see https://olvlvl.com/2018-01-delayed-event-dispatcher
 */
class DelayedEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    private $eventDispatcher;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var callable
     */
    private $delayArbiter;

    /**
     * @var callable
     */
    private $exceptionHandler;

    /**
     * @var callable
     */
    private $flusher;

    /**
     * @var object[]
     */
    private $queue = [];

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param boolean $disabled
     * @param callable|null $delayArbiter The delay arbiter determines whether an event should be delayed or not. It's
     *     a callable with the following signature: `function($event, string $eventName = null): bool`. The
     *     default delay arbiter just returns `true`, all events are delayed. Note: The delay arbiter is only invoked
     *     if delaying events is enabled.
     * @param callable|null $exceptionHandler This callable handles exceptions thrown during event dispatching. It's a
     *     callable with the following signature:
     *     `function(\Throwable $exception, $event, string $eventName = null): void`. The default exception
     *     handler just throws the exception.
     * @param callable|null $flusher By default, delayed events are dispatched with the decorated event dispatcher
     *     when flushed, but you can choose another solution entirely, like sending them to consumers using RabbitMQ or
     *     Kafka. The callable has the following signature: `function($event, string $eventName = null): void`.
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        bool $disabled = false,
        callable $delayArbiter = null,
        callable $exceptionHandler = null,
        callable $flusher = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->enabled = !$disabled;
        $this->delayArbiter = $delayArbiter ?: static function () {
            return true;
        };
        $this->exceptionHandler = $exceptionHandler ?: static function (Throwable $exception) {
            throw $exception;
        };
        $this->flusher = $flusher ?: function (object $event): object {
            return $this->eventDispatcher->dispatch($event);
        };
    }

    /**
     * @inheritdoc
     */
    public function dispatch($event): object
    {
        if ($this->shouldDelay($event)) {
            $this->queue[] = $event;

            return $event;
        }

        return $this->eventDispatcher->dispatch($event);
    }

    /**
     * Dispatch all the events in the queue.
     *
     * Note: Exceptions raised during dispatching are caught and forwarded to the exception handler defined during
     * construct.
     *
     * @return void
     */
    public function flush(): void
    {
        while (($event = array_shift($this->queue))) {
            try {
                ($this->flusher)($event);
            } catch (Throwable $e) {
                ($this->exceptionHandler)($e, $event);
            }
        }
    }

    /**
     * @return object[]
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * Должно быть отложено или нет.
     *
     * @param object $event Событие.
     *
     * @return boolean
     */
    private function shouldDelay($event): bool
    {
        return $this->enabled && ($this->delayArbiter)($event);
    }
}
