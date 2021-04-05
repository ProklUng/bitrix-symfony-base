<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle\DataCollector;

use GuzzleHttp\Exception\RequestException;
use Local\Bundles\GuzzleBundle\Middlewares\Cache\CacheMiddleware;
use Local\Bundles\GuzzleBundle\Middlewares\Cache\MockMiddleware;
use Local\Bundles\GuzzleBundle\Middlewares\History\History;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Csa Guzzle Collector.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
// @phpstan-ignore-next-line
abstract class InternalGuzzleCollector extends DataCollector
{
    public const MAX_BODY_SIZE = 0x10000;

    /**
     * @var integer $maxBodySize
     */
    private $maxBodySize;

    /**
     * @var History|null $history
     */
    private $history;

    /**
     * @phpstan-ignore-next-line
     * @var \Namshi\Cuzzle\Formatter\CurlFormatter|null
     */
    private $curlFormatter;

    /**
     * Constructor.
     *
     * @param int $maxBodySize The max body size to store in the profiler storage
     */
    public function __construct($maxBodySize = self::MAX_BODY_SIZE, History $history = null)
    {
        $this->maxBodySize = $maxBodySize;
        $this->history = $history ?: new History();

        if (class_exists(\Namshi\Cuzzle\Formatter\CurlFormatter::class)) {
            $this->curlFormatter = new \Namshi\Cuzzle\Formatter\CurlFormatter();
        }

        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function doCollect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $data = [];

        foreach ($this->history as $requestItem) {
            /* @var RequestInterface $requestItem */
            $transaction = $this->history[$requestItem];
            /* @var \Psr\Http\Message\ResponseInterface $response */
            $response = $transaction['response'];
            /* @var \Exception $error */
            $error = $transaction['error'];
            /* @var array $info */
            $info = $transaction['info'];

            $req = [
                'request' => [
                    'method' => $requestItem->getMethod(),
                    'version' => $requestItem->getProtocolVersion(),
                    'headers' => $requestItem->getHeaders(),
                    'body' => $this->cropContent($requestItem->getBody()),
                ],
                'info' => $info,
                'uri' => urldecode($requestItem->getUri()),
                'httpCode' => 0,
                'error' => null,
            ];

            if ($this->curlFormatter && $requestItem->getBody()->getSize() <= $this->maxBodySize) {
                // @phpstan-ignore-next-line
                $req['curl'] = $this->curlFormatter->format($requestItem);
            }

            if ($response) {
                $req['response'] = [
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders(),
                    'body' => $this->cropContent($response->getBody()),
                ];

                $req['httpCode'] = $response->getStatusCode();

                if ($response->hasHeader(CacheMiddleware::DEBUG_HEADER)) {
                    $req['cache'] = $response->getHeaderLine(CacheMiddleware::DEBUG_HEADER);
                }

                if ($response->hasHeader(MockMiddleware::DEBUG_HEADER)) {
                    $req['mock'] = $response->getHeaderLine(MockMiddleware::DEBUG_HEADER);
                }
            }

            if ($error && $error instanceof RequestException) {
                $req['error'] = [
                    'message' => $error->getMessage(),
                    'line' => $error->getLine(),
                    'file' => $error->getFile(),
                    'code' => $error->getCode(),
                    'trace' => $error->getTraceAsString(),
                ];
            }

            $data[] = $req;
        }

        $this->data = $data;
    }

    /**
     * @param StreamInterface|null $stream
     *
     * @return string
     */
    private function cropContent(StreamInterface $stream = null): string
    {
        if (null === $stream) {
            return '';
        }

        if ($stream->getSize() <= $this->maxBodySize) {
            return (string) $stream;
        }

        $stream->seek(0);

        return '(partial content)'.$stream->read($this->maxBodySize).'(...)';
    }

    /**
     * @return array|Data
     */
    public function getErrors()
    {
        return array_filter($this->data, static function ($call) : bool {
            return 0 === $call['httpCode'] || $call['httpCode'] >= 400;
        });
    }

    /**
     * @return float|int
     */
    public function getTotalTime()
    {
        return array_sum(
            array_map(
                static function ($call) {
                    return $call['info']['total_time'] ?? 0;
                },
                $this->data
            )
        );
    }

    public function getCalls(): array
    {
        return $this->data;
    }

    /**
     * @deprecated This method is deprecated since version 2.2. It will be removed in version 3.0
     *
     * @return History
     */
    public function getHistory() : History
    {
        return $this->history;
    }

    /**
     * {@inheritdoc}
     */
    public function reset() : void
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'guzzle';
    }
}

// @phpstan-ignore-next-line
if (Kernel::MAJOR_VERSION >= 5) {
    final class GuzzleCollector extends InternalGuzzleCollector
    {
        /**
         * @param Request $request
         * @param Response $response
         * @param \Throwable|null $exception
         */
        public function collect(Request $request, Response $response, \Throwable $exception = null) : void
        {
            parent::doCollect($request, $response, $exception);
        }
    }
}
// @phpstan-ignore-next-line
else {
    class GuzzleCollector extends InternalGuzzleCollector
    {
        /**
         * @param Request $request
         * @param Response $response
         * @param \Exception|null $exception
         */
        // @phpstan-ignore-next-line
        public function collect(Request $request, Response $response, \Exception $exception = null) : void
        {
            parent::doCollect($request, $response, $exception);
        }
    }
}
