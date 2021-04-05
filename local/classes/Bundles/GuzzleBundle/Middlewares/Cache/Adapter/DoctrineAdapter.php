<?php

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter;

use Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy\HashNamingStrategy;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Psr7\Response;
use Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy\NamingStrategyInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DoctrineAdapter implements StorageAdapterInterface
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var HashNamingStrategy|NamingStrategyInterface|null $namingStrategy
     */
    private $namingStrategy;

    /**
     * @var integer $ttl
     */
    private $ttl;

    /**
     * @param Cache $cache
     * @param int $ttl
     * @param NamingStrategyInterface|null $namingStrategy
     */
    public function __construct(Cache $cache, $ttl = 0, NamingStrategyInterface $namingStrategy = null)
    {
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

        if ($this->cache->contains($key)) {
            $data = $this->cache->fetch($key);

            return new Response($data['status'], $data['headers'], $data['body'], $data['version'], $data['reason']);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request, ResponseInterface $response) : void
    {
        $data = [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string)$response->getBody(),
            'version' => $response->getProtocolVersion(),
            'reason' => $response->getReasonPhrase(),
        ];

        $this->cache->save($this->namingStrategy->filename($request), $data, $this->ttl);

        $response->getBody()->seek(0);
    }
}
