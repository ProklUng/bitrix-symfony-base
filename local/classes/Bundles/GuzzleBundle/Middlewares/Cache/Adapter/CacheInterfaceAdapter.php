<?php

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter;

use Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy\HashNamingStrategy;
use Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy\NamingStrategyInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class CacheInterfaceAdapter
 * @package Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter
 *
 * @since 15.11.2020
 */
class CacheInterfaceAdapter implements StorageAdapterInterface
{
    /**
     * @var CacheInterface $cache Кэшер.
     */
    private $cache;

    /**
     * @var HashNamingStrategy|NamingStrategyInterface $namingStrategy Нэйминг.
     */
    private $namingStrategy;

    /** @var integer $ttl Время жизни кэша. */
    private $ttl;

    /**
     * @param CacheInterface               $cache          Кэшер.
     * @param integer                      $ttl            Время жизни кэша.
     * @param NamingStrategyInterface|null $namingStrategy Нэйминг.
     */
    public function __construct(
        CacheInterface $cache,
        int $ttl = 0,
        NamingStrategyInterface $namingStrategy = null
    ) {
        $this->cache = $cache;
        $this->namingStrategy = $namingStrategy ?: new HashNamingStrategy();
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request) : ?ResponseInterface
    {
        $key = $this->namingStrategy->filename($request);

        $data = $this->cache->get($key);

        if ($data) {
            return new Response($data['status'], $data['headers'], $data['body'], $data['version'], $data['reason']);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request, ResponseInterface $response) : void
    {
        $key = $this->namingStrategy->filename($request);

        $this->cache->set($key, [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string)$response->getBody(),
            'version' => $response->getProtocolVersion(),
            'reason' => $response->getReasonPhrase(),
        ],
            $this->ttl
        );

        $response->getBody()->seek(0);
    }
}
