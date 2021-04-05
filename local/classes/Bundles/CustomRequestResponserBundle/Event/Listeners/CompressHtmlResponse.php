<?php

namespace Local\Bundles\CustomRequestResponserBundle\Event\Listeners;

use Local\Bundles\CustomRequestResponserBundle\Event\Interfaces\OnKernelResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class CompressHtmlResponse
 *
 * @package Local\Bundles\CustomRequestResponserBundle\Event\Listeners
 *
 * @since 22.12.2020
 */
class CompressHtmlResponse implements OnKernelResponseHandlerInterface
{
    /**
     * Событие kernel.response.
     *
     * Минификация Response для txt/html ответа.
     *
     * @param ResponseEvent $event Объект события.
     *
     * @return void
     *
     */
    public function handle(ResponseEvent $event): void
    {
        // Фильтрация внешних нативных маршрутов.
        if (!$event->isMasterRequest()
            ||
            $event->getResponse()->getStatusCode() === 404
        ) {
            return;
        }

        $response = $event->getResponse();

        $contentType = $response->headers->get('content-type');

        if (is_array($contentType)) {
            $contentType = reset($contentType);
        }

        if (get_class($response) === Response::class
            && ($contentType === null || strpos($contentType, 'text/html') === 0)
        ) {
            $response->setContent(
                trim(preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/',
                    PHP_EOL,
                    preg_replace('/\h+/u', ' ',
                        $response->getContent())))
            );
        }
    }
}
