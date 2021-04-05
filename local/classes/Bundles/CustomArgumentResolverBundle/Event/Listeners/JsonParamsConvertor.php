<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnKernelRequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class JsonParamsConvertor
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 27.10.2020
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class JsonParamsConvertor implements OnKernelRequestHandlerInterface
{
    /**
     * Событие kernel.request.
     *
     * Преобразовать json параметры запроса в нормальные аттрибуты.
     *
     * @param RequestEvent $event Объект события.
     *
     * @return void
     *
     * @since 10.09.2020
     */
    public function handle(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->isAvailable($request) === false) {
            return;
        }

        if ($this->transform($request) === false) {
            $response = new Response('Unable to parse request.', Response::HTTP_BAD_REQUEST);

            $event->setResponse($response);
        }
    }

    /**
     * Json в запросе?
     *
     * @param Request $request Request.
     *
     * @return boolean
     */
    private function isAvailable(Request $request): bool
    {
        return $request->getContentType() === 'json' && $request->getContent();
    }

    /**
     * Преобразование.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function transform(Request $request): bool
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if (is_array($data)) {
            $request->request->replace($data);
        }

        return true;
    }
}