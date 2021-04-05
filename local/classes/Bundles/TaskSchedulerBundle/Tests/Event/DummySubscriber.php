<?php

namespace Local\Bundles\TaskSchedulerBundle\Tests\Event;

use Local\Bundles\TaskSchedulerBundle\Event\EventSubscriberInterface;

class DummySubscriber implements EventSubscriberInterface
{
    public $args;

    public function callFoo()
    {
        $this->args = func_get_args();
    }

    public static function getEvents(): array
    {
        return [
            "foo" => "callFoo"
        ];
    }
}