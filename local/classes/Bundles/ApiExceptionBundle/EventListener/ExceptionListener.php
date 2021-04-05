<?php

namespace Local\Bundles\ApiExceptionBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as SymfonyHttpExceptionInterface;
use Local\Bundles\ApiExceptionBundle\Manager\ExceptionManager;
use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\ExceptionInterface;
use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\HttpExceptionInterface;
use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\FlattenErrorExceptionInterface;
use Throwable;

/**
 * Class ExceptionListener
 * @package Local\Bundles\ApiExceptionBundle\EventListener
 */
class ExceptionListener
{
    /**
     * @var boolean $stackTrace Добавлять stacktrace?
     */
    protected $stackTrace;

    /**
     * @var array $default Значения по умолчанию.
     */
    protected $default;

    /**
     * @var ExceptionManager $exceptionManager Менеджер исключений.
     */
    protected $exceptionManager;

    /**
     * @var boolean $matchAll Применять на ВСЕ роуты?
     */
    protected $matchAll;

    /**
     * Constructor ExceptionListener
     *
     * @param ExceptionManager $exceptionManager Менеджер исключений.
     * @param boolean          $matchAll         Применять на ВСЕ роуты?
     * @param array            $default          Значения по умолчанию.
     * @param boolean          $stackTrace       Добавлять stacktrace?
     */
    public function __construct(
        ExceptionManager $exceptionManager,
        $matchAll,
        array $default,
        $stackTrace = false
    ) {
        $this->exceptionManager = $exceptionManager;
        $this->matchAll         = $matchAll;
        $this->default          = $default;
        $this->stackTrace       = $stackTrace;
    }

    /**
     * Format response exception.
     * Привел к своему кастомному формату.
     *
     * @param ExceptionEvent $event Объект события.
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event) : void
    {
        $exception = $event->getThrowable();

        if ($this->matchAll === false && !$this->isApiException($exception)) {
            return;
        }

        $data = [];

        if ($this->isApiException($exception)) {
            $exception = $this->exceptionManager->configure($exception);
        }

        $statusCode = $this->getStatusCode($exception);
        $data['error'] = true;
        $data['status']  = $statusCode;

        if ($code = $exception->getCode()) {
            $data['code'] = $code;
        }

        $data['message'] = $this->getMessage($exception);

        if ($this->isFlattenErrorException($exception)) {
            // @phpstan-ignore-next-line
            $data['errors'] = $exception->getFlattenErrors();
        }

        if ($this->stackTrace) {
            $data['stack_trace'] = $exception->getTrace();

            // Clean stacktrace to avoid circular reference or invalid type
            array_walk_recursive(
                $data['stack_trace'],
                static function (&$item) : void {
                    if (is_object($item)) {
                        $item = get_class($item);
                    } elseif (is_resource($item)) {
                        $item = get_resource_type($item);
                    }
                }
            );
        }

        $response = new JsonResponse($data, $statusCode, $this->getHeaders($exception));

        $event->setResponse($response);
    }

    /**
     * Get exception status code
     *
     * @param Throwable $exception Exception.
     *
     * @return integer
     */
    private function getStatusCode(Throwable $exception): int
    {
        $statusCode = (int)$this->default['status'];

        if ($exception instanceof SymfonyHttpExceptionInterface
            || $exception instanceof HttpExceptionInterface
        ) {
            $statusCode = $exception->getStatusCode();
        }

        return $statusCode;
    }

    /**
     * Get exception message
     *
     * @param Throwable $exception Exception.
     *
     * @return mixed
     */
    private function getMessage(Throwable $exception)
    {
        $message = $exception->getMessage();

        if ($this->isApiException($exception)) {
            // @phpstan-ignore-next-line
            $message = $exception->getMessageWithVariables();
        }

        return $message;
    }

    /**
     * Get exception headers
     *
     * @param Throwable $exception Exception.
     *
     * @return array
     */
    private function getHeaders(Throwable $exception): array
    {
        $headers = (array)$this->default['headers'];

        if ($exception instanceof SymfonyHttpExceptionInterface
            || $exception instanceof HttpExceptionInterface
        ) {
            $headers = $exception->getHeaders();
        }

        return $headers;
    }

    /**
     * Is api exception
     *
     * @param Throwable $exception Exception.
     *
     * @return boolean
     */
    private function isApiException(Throwable $exception): bool
    {
        return $exception instanceof ExceptionInterface;
    }

    /**
     * Is flatten error exception
     *
     * @param Throwable $exception Exception.
     *
     * @return boolean
     */
    private function isFlattenErrorException(Throwable $exception): bool
    {
        return $exception instanceof FlattenErrorExceptionInterface;
    }
}
