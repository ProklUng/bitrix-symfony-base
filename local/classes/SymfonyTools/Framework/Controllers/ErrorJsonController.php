<?php

namespace Local\SymfonyTools\Framework\Controllers;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ErrorJsonController
 * @package Local\SymfonyTools\Framework\Controllers
 *
 * @since 09.09.2020
 */
class ErrorJsonController implements ErrorControllerInterface
{
    /**
     * @var SerializerInterface $serializer Сериализатор.
     */
    private $serializer;

    /**
     * ErrorJsonController constructor.
     *
     * @param SerializerInterface $serializer Сериализатор.
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Обработчик ошибок. На выходе JSON.
     *
     * @param FlattenException $exception Исключение.
     *
     * @return Response
     */
    public function exceptionAction(FlattenException $exception): Response
    {
        $arResult = [
            'error' => true,
            'message' => $exception->getMessage()
        ];

        return new Response(
            $this->serializer->serialize($arResult, 'json'),
            $exception->getStatusCode(),
            ['Content-Type: application/json; charset=utf-8']
        );
    }
}
