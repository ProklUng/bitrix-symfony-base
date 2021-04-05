<?php
/**
 * String response listener
 *
 * if the response is a string, convert it to a proper Response object
 *
 * @package      wp-symfony-router
 * @subpackage   framework
 * @since        0.0.1
 * @author       Josh Visick <josh@visickdesign.net>
 */

namespace Local\Util\Router;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;

/**
 * Class StringResponseListener
 * @package Local\Util\Router
 */
class StringResponseListener implements EventSubscriberInterface
{

    /**
     * @param ViewEvent $obEvent Событие.
     *
     * @return void
     */
    public function onView(ViewEvent $obEvent)
    {
        // Результат работы.
        $sResponse = $obEvent->getControllerResult();

        if (is_string($sResponse)) {
            $obEvent->setResponse(new Response($sResponse));
        }
    }

    /**
     * Подписка на события.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array('kernel.view' => 'onView');
    }
}
