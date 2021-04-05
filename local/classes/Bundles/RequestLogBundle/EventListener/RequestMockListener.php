<?php

namespace Local\Bundles\RequestLogBundle\EventListener;

use Local\Bundles\RequestLogBundle\Service\ResponseLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class RequestMockListener
 * @package Local\Bundles\RequestLogBundle\EventListener
 *
 * @since 06.03.2021
 */
class RequestMockListener
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
     * RequestMockListener constructor.
     *
     * @param ResponseLogger $responseLogger Логгер.
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
     * Обработчик события.
     *
     * @param RequestEvent $event Событие.
     *
     * @return void
     */
    public function handle(RequestEvent $event) : void
    {
        $request = $event->getRequest();

        if (!$event->isMasterRequest()) {
            return;
        }

        $pathMock = $this->responseLogger->getMocksDir() . $this->responseLogger->getFilePathByRequest($request);

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
            $event->setResponse($response);
        }
    }
}