<?php

namespace Local\Bundles\RequestLogBundle\Service\BitrixBridge;

use Bitrix\Main\Application;
use CMain;
use Local\Bundles\RequestLogBundle\Service\ResponseLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResponseTransformer
 * @package Local\Bundles\RequestLogBundle\Service\BitrixBridge
 *
 * @since 06.03.2021
 */
class ResponseCacher
{
    /**
     * @var ResponseLogger $responseLogger Логгер.
     */
    private $responseLogger;

    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * ResponseTransformer constructor.
     *
     * @param ResponseLogger $responseLogger Логгер ответов.
     * @param Filesystem     $filesystem     Файловая система.
     */
    public function __construct(
        ResponseLogger $responseLogger,
        Filesystem $filesystem
    ) {
        $this->responseLogger = $responseLogger;
        $this->filesystem = $filesystem;
    }

    /**
     * @return void
     */
    public function handle() : void
    {
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        $symfonyRequest = Request::create(
            $request->getRequestUri(),
            $request->getRequestMethod(),
            $request->getQueryList()->toArray(),
            $request->getCookieList()->toArray(),
            $request->getFileList()->toArray(),
            $request->getServer()->toArray(),
        );

        $pathMock = $this->responseLogger->getMocksDir()
            .
            $this->responseLogger->getFilePathByRequest($symfonyRequest);

        if (!$this->filesystem->exists($pathMock)) {
            return;
        }

        // Достать мок, вернуть десериализованный Response.
        $content = (string)file_get_contents($pathMock);
        $data = json_decode($content, true);

        if ($data['response']['serialized_response']) {
            /** @var Response $response */
            $response = unserialize($data['response']['serialized_response']);
            // Пометить Response восстановленным из мока.
            $response->headers->set('x-generated-response-mock', 'true');
            $response->sendHeaders();

            // Send the response to the browser and exit app.
            CMain::FinalActions((string)$response->getContent());
            exit;
        }
    }

}