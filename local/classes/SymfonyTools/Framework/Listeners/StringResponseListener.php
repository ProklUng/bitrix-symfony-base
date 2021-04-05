<?php
/**
 * String response listener
 *
 * if the response is a string, convert it to a proper Response object
 *
 */

namespace Local\SymfonyTools\Framework\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StringResponseListener
 * @package Local\SymfonyTools\Framework\Listeners
 *
 * @since 05.09.2020
 */
class StringResponseListener implements EventSubscriberInterface
{
    /**
     * if the response is a string, convert it to a proper Response object
     *
     * @param ViewEvent $event
     */
    public function onView(ViewEvent $event) : void
    {
        $response = $event->getControllerResult();

        if (is_string($response)) {
            $event->setResponse(new Response($response));
        }
    }

    /**
     * Подписчик на событие.
     *
     * @return array
     */
    public static function getSubscribedEvents() : array
    {
        return ['kernel.view' => 'onView'];
    }
}
