<?php

namespace Local\Bundles\CustomRequestResponserBundle\Event\Listeners;

use Local\Bundles\CustomRequestResponserBundle\Event\Interfaces\OnKernelResponseHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class LogResponse
 *
 * @package Local\Bundles\CustomRequestResponserBundle\Event\Listeners
 *
 * @since 06.03.2021
 *
 * В параметрах роута:
 *
 * defaults:
 *     _log_response:
 *            log_content: false # Логгировать контент ответа.
 */
final class LogResponse implements OnKernelResponseHandlerInterface
{
    /**
     * @var LoggerInterface $logger Логгер
     */
    private $logger;

    /**
     * LogResponse constructor.
     *
     * @param LoggerInterface $logger Логгер.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
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

        $request = $event->getRequest();

        $param = $request->attributes->get('_log_response', []);
        if (!$param) {
            return;
        }

        // Нужно ли логгировать контент.
        $needLogContent = array_key_exists('log_content', $param) ?
            $param['log_content'] : false;

        $path = $request->getBasePath();
        $contentType = $request->getContentType();
        $clientIp = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');

        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();

        $requestJsonContent = json_decode($request->getContent(), true);

        $message = \sprintf(
            'Response %s for "%s %s"',
            $statusCode,
            $request->getMethod(),
            $request->getRequestUri()
        );

        $this->logResponse(
            $message,
            [
                'method' => $request->getMethod(),
                'path' => $path,
                'uri' => $request->getRequestUri(),
                'content_request' => $requestJsonContent ?: $request->getContent(),
                'param_request' => $this->getParamsRequest($request),
                'content-type-request' => $contentType,
                'content-type-response' => $response->headers->get('Content-Type'),
                'latency' => $this->getTime($request),
                'client-ip' => $clientIp,
                'status_code' => $statusCode,
                'user-agent' => $userAgent,
                'content_response' => $needLogContent ? $event->getResponse()->getContent() : ''
            ]
        );
    }

    /**
     * Параметры запроса.
     *
     * @param Request $request Request.
     *
     * @return array
     */
    private function getParamsRequest(Request $request) : array
    {
        // Тип запроса.
        $typeRequest = $request->getMethod();

        return ($typeRequest === 'POST') ?
            $request->request->all()
            :
            $request->query->all();
    }

    /**
     * @param Request $request Request.
     *
     * @return float
     */
    private function getTime(Request $request): float
    {
        if (!$request->server) {
            return 0;
        }

        $startTime = $request->server->get(
            'REQUEST_TIME_FLOAT',
            $request->server->get('REQUEST_TIME')
        );
        $time = microtime(true) - $startTime;

        return (float)round($time * 1000);
    }

    /**
     * Логгирование.
     *
     * @param string $message Сообщение.
     * @param array  $fields  Данные.
     *
     * @return void
     */
    private function logResponse(string $message, array $fields): void
    {
        $this->logger->info($message, $fields);
    }
}
