<?php

namespace Local\SymfonyTools\Framework\Controllers;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface ErrorControllerInterface
 * @package Local\SymfonyTools\Framework\Controllers
 *
 * @since 09.09.2020
 */
interface ErrorControllerInterface
{
    /**
     * Обработчик ошибок.
     *
     * @param FlattenException $exception Исключение.
     *
     * @return Response
     */
    public function exceptionAction(FlattenException $exception): Response;
}
