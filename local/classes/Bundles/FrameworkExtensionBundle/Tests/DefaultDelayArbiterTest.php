<?php

namespace Local\Bundles\FrameworkExtensionBundle\Tests;

use Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents\DefaultDelayArbiter;
use Local\Bundles\FrameworkExtensionBundle\Services\DelayedEvents\Delayable;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @since 13.04.2021
 */
class DefaultDelayArbiterTest extends TestCase
{
    /**
     * @var DefaultDelayArbiter
     */
    private $delayArbiter;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->delayArbiter = new DefaultDelayArbiter();

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testIsNotDelayableEvent() : void
    {
        $event = new Event();

        $class = $this->delayArbiter;
        $result = $class($event, 'test');

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testIsDelayableEvent() : void
    {
        $event = new class extends Event implements Delayable {

        };

        $class = $this->delayArbiter;
        $result = $class($event, 'test');

        $this->assertTrue($result);
    }
}
