<?php

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache;

use Closure;
use Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter\StorageAdapterInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Cache Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class CacheMiddleware
{
    public const DEBUG_HEADER = 'X-Guzzle-Cache';
    public const DEBUG_HEADER_HIT = 'HIT';
    public const DEBUG_HEADER_MISS = 'MISS';

    /**
     * @var StorageAdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var boolean $debug
     */
    protected $debug;

    /**
     * CacheMiddleware constructor.
     *
     * @param StorageAdapterInterface $adapter
     * @param boolean                 $debug
     */
    public function __construct(StorageAdapterInterface $adapter, $debug = false)
    {
        $this->adapter = $adapter;
        $this->debug = $debug;
    }

    /**
     * @param callable $handler Обработчик.
     *
     * @return Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if (!$response = $this->adapter->fetch($request)) {
                return $this->handleSave($handler, $request, $options);
            }

            $response = $this->addDebugHeader($response, static::DEBUG_HEADER_HIT);

            return new FulfilledPromise($response);
        };
    }

    /**
     * @param callable         $handler Обработчик.
     * @param RequestInterface $request Request.
     * @param array            $options Опции.
     *
     * @return mixed
     */
    protected function handleSave(callable $handler, RequestInterface $request, array $options)
    {
        return $handler($request, $options)->then(
            function (ResponseInterface $response) use ($request) : ResponseInterface {
                $this->adapter->save($request, $response);

                return $this->addDebugHeader($response, static::DEBUG_HEADER_MISS);
            }
        );
    }

    /**
     * @param ResponseInterface $response Response.
     * @param mixed             $value    Значение.
     *
     * @return ResponseInterface
     */
    protected function addDebugHeader(ResponseInterface $response, $value): ResponseInterface
    {
        if (!$this->debug) {
            return $response;
        }

        return $response->withHeader(static::DEBUG_HEADER, $value);
    }
}
