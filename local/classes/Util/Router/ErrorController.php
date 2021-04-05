<?php
namespace Local\Util\Router;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorController
 * Обработка ошибок роутера.
 * @package Local\Util\Router
 */
class ErrorController
{
    /**
     * Обработка ошибок.
     *
     * @param FlattenException $exception Исключение ошибки.
     *
     * @return Response
     */
    public function exceptionAction(FlattenException $exception)
    {
        $msg = 'Something went wrong! ('.$exception->getMessage().')';

        return new Response($msg, $exception->getStatusCode());
    }
}
