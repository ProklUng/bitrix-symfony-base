<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Traits;

use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AbstractSubscriberKernelRequestTrait
 * Общие методы для подписчиков на события kernel.request.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Traits
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 */
trait AbstractSubscriberKernelRequestTrait
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => 'handle'
        ];
    }
}
