<?php

namespace Local\Services\Responses;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class JsonResponse
 * @package Local\Services\Responses
 */
class JsonResponseError
{
    /**
     * @var Response
     */
    private $response;

    /** @var integer $httpErrorCode Код ошибки. По умолчанию 400 - Bad request. */
    private $httpErrorCode;

    /**
     * JsonResponse constructor.
     *
     * @param Response $response      Пустой Response.
     * @param integer  $httpErrorCode Код ошибки. По умолчанию 400 - Bad request.
     */
    public function __construct(
        Response $response,
        int $httpErrorCode = 400
    ) {
        $this->response = $response;
        $this->httpErrorCode = $httpErrorCode;
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
        $this->response->setStatusCode($this->httpErrorCode);
        $this->response->send();
    }
}
