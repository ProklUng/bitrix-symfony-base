<?php

namespace Tests\Events\Samples;

use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Local\Bundles\CustomArgumentResolverBundle\Event\Traits\AbstractSubscriberTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SampleSubscriber
 * @package Tests\Events\Samples
 *
 * @since 10.10.2020
 */
class SampleSubscriber implements EventSubscriberInterface, OnControllerRequestHandlerInterface
{
    use AbstractSubscriberTrait;

    public function handle(ControllerEvent $event): void
    {

    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['handle', 15]
            ],
        ];
    }
}
