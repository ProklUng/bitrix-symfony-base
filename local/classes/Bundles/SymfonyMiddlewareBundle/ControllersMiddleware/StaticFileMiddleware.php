<?php

namespace Local\Bundles\SymfonyMiddlewareBundle\ControllersMiddleware;

use Local\Bundles\SymfonyMiddlewareBundle\ControllersMiddleware\Utils\ContentTypeMapping;
use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class StaticFileMiddleware
 * @package Local\Services\ControllerMiddleware
 *
 * @since 23.02.2021
 */
class StaticFileMiddleware implements MiddlewareInterface
{
    /**
     * @var string $publicDirectory Директория, где лежат файлы.
     */
    private $publicDirectory;

    /**
     * @var string $hashAlgorithm Алгоритм хэширования.
     */
    private $hashAlgorithm;

    /**
     * StaticFileMiddleware constructor.
     *
     * @param string $publicDirectory Директория, где лежат файлы.
     * @param string $hashAlgorithm   Алгоритм хэширования.
     */
    public function __construct(
        string $publicDirectory = '',
        string $hashAlgorithm = 'md5'
    ) {
        $this->publicDirectory = $publicDirectory;
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request): ?Response
    {
        if (!in_array($this->hashAlgorithm, hash_algos(), true)) {
            throw new LogicException(sprintf('Invalid or not supported hash algorithm: "%s"', $this->hashAlgorithm));
        }

        if ($this->publicDirectory === '') {
            throw new LogicException(
                sprintf('Public upload directory not configured. You forget initialize StaticFileMiddleware as service?')
            );
        }

        $target = $this->getRequestTarget($request);
        if ($target === '') {
            return null;
        }

        $filename = $this->publicDirectory . $target;

        $hash = hash_file($this->hashAlgorithm, $filename);

        if (!is_readable($filename)) {
            return null;
        }

        $response = new StreamedResponse();

        if ($request->headers->get('If-None-Match') === $hash) {
            $response->setStatusCode(304);
        }

        $response->headers->set('Content-Length', (string) filesize($filename));
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.$target.'"'
        );
        $response->headers->set('ETag', $hash);

        $this->addContentType($response, $target);

        $response->setCallback(function () use ($filename) {
            echo (string)file_get_contents($filename);
        });

        return $response;
    }

    /**
     * @param Request $request Request.
     *
     * @return string
     */
    private function getRequestTarget(Request $request) : string
    {
        $target = '';

        if ($request->getQueryString()) {
            $target = $request->getQueryString();
            parse_str($target, $result);
            $target = (string)$result['file'];
        }

        return $target;
    }

    /**
     * Добавить content type.
     *
     * @param Response $response Response.
     * @param string   $filename Имя файла.
     *
     * @return Response
     */
    private function addContentType(Response $response, string $filename): Response
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (array_key_exists($extension, ContentTypeMapping::CONTENT_TYPE_MAPPING)) {
            $response->headers->set('Content-Type', ContentTypeMapping::CONTENT_TYPE_MAPPING[$extension]);
        }

        return $response;
    }
}