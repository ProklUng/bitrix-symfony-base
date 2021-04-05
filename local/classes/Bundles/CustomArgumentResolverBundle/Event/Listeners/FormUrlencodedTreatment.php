<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use JsonException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnKernelRequestHandlerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class FormUrlencodedTreatment
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 11.09.2020 Доработка.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 * @since 06.12.2020 Убрал зависимость от функции WP.
 */
class FormUrlencodedTreatment implements OnKernelRequestHandlerInterface
{
    /**
     * Событие kernel.request.
     *
     * Особое обращение с данными, прикидывающимися формой.
     *
     * @param RequestEvent $event Объект события.
     *
     * @return void
     *
     * @throws JsonException Ошибки JSON.
     *
     * @since 10.09.2020
     * @since 11.09.2020 Доработка.
     * @since 06.12.2020 Убрал зависимость от функции WP.
     */
    public function handle(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        $header = $request->headers->get('content-type');
        if (($header === 'application/x-www-form-urlencoded'
                ||
                $header === 'application/json')
            &&
            $request->getContent()
        ) {
            // $_POST данные в массив.
            $arPostData = (array)json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $arPostData = json_decode(
                json_encode($arPostData, JSON_THROW_ON_ERROR, 512),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $result = $this->arrayOfStrings($arPostData);

            $request->request->replace($result);
        }
    }

    /**
     * Рекурсивная очистка массивов
     *
     * @param mixed $array Массив.
     *
     * @return array OK or NULL.
     */
    private function arrayOfStrings($array): array
    {
        $result = [];
        foreach ((array)$array as $key => $item) {
            $result[$key] = is_array($item) ? $this->arrayOfStrings($item) : $item;
        }

        return $result;
    }
}
