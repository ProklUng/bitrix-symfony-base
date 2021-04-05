<?php

namespace Local\Services\Responses;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class JsonResponse
 * @package Local\Services\Responses
 */
class JsonResponse
{
    /**
     * @var Response
     */
    private $response;

    /**
     * JsonResponse constructor.
     *
     * @param Response $response Пустой Response.
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Ответ.
     *
     * @param array $arData Данные.
     *
     * @return void
     */
    public function response(array $arData = [])
    {
        $this->response->setContent(json_encode($arData));
        $this->response->headers->set('Content-Type', 'application/json');
        $this->response->send();
    }
}
