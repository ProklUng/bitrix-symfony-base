<?php

namespace Local\Bundles\RequestLogBundle\Service\BitrixBridge;

use Bitrix\Main\Application;
use CHTTP;
use Local\Bundles\RequestLogBundle\Service\ResponseLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResponseTransformer
 * @package Local\Bundles\RequestLogBundle\Service\BitrixBridge
 */
class ResponseTransformer
{
    /**
     * @var ResponseLogger $responseLogger Логгер.
     */
    private $responseLogger;

    /**
     * @var array $urls Паттерны URL, подлежащие кэшированию.
     */
    private $urls;

    /**
     * ResponseTransformer constructor.
     *
     * @param ResponseLogger $responseLogger Логгер ответов.
     * @param array          $urls           Паттерны URL, подлежащие кэшированию.
     */
    public function __construct(
        ResponseLogger $responseLogger,
        array $urls = []
    ) {
        $this->responseLogger = $responseLogger;
        $this->urls = $urls;
    }

    /**
     * Движуха.
     *
     * @return void
     */
    public function handle() : void
    {
        $context = Application::getInstance()->getContext();

        $request = $context->getRequest();
        $response = $context->getResponse();

        if (!$this->needProcess($request->getRequestUri())) {
            return;
        }

        $symfonyRequest = Request::create(
            $request->getRequestUri(),
            $request->getRequestMethod(),
            $request->getQueryList()->toArray(),
            $request->getCookieList()->toArray(),
            $request->getFileList()->toArray(),
            $request->getServer()->toArray(),
        );

        $symfonyResponse = new Response();
        $symfonyResponse->setContent($response->getContent());

        $status = CHTTP::GetLastStatus();
        if ($status === '404 Not Found') {
            $symfonyResponse->setStatusCode(404);
        }

        $symfonyResponse->headers->add($response->getHeaders()->toArray());

        // Восстановленный из мока Response.
        if (strlen($response->getHeaders()->get('x-generated-response-mock')) > 0) {
            return;
        }

        $this->responseLogger->logResponse($symfonyRequest, $symfonyResponse);
    }

    /**
     * Нужно ли обрабатывать этот URL.
     *
     * @param string $uri URL.
     *
     * @return boolean
     */
    private function needProcess(string $uri) : bool
    {
        if (stripos($uri, '/bitrix/') !== false) {
            return false;
        }

        $needProcess = false;
        foreach ($this->urls as $url) {
            if (preg_match($url, $uri)) {
                $needProcess = true;
            }
        }

        return $needProcess;
    }
}