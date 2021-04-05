<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Examples;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SampleSubscriber
 * @package Local\Bundles\CustomArgumentResolverBundle
 *
 * @since 11.09.2020
 */
class SampleSubscriber implements EventSubscriberInterface
{
    /**
     * @param RequestEvent $event
     *
     * @return void
     */
    public function handle(RequestEvent $event): void
    {
        // echo 'OK';
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => [
                ['handle', 10]
            ],
        ];
    }
}
