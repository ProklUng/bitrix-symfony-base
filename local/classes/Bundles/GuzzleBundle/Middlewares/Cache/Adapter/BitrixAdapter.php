<?php

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter;

use CPHPCache;
use GuzzleHttp\Psr7\Response;
use Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy\HashNamingStrategy;
use Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy\NamingStrategyInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BitrixAdapter
 * @package Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter
 *
 * @simce 14.11.2020
 * @since 02.12.2020 Исключить кэширование PUT & DELETE запросов.
 */
class BitrixAdapter implements StorageAdapterInterface
{
    /**
     * @var CPHPCache $cacher Кэшер.
     */
    private $cache;

    /**
     * @var HashNamingStrategy|NamingStrategyInterface|null $namingStrategy Стратегия наименования.
     */
    private $namingStrategy;

    /**
     * @var integer $ttl Время жизни кэша.
     */
    private $ttl;

    /**
     * BitrixAdapter constructor.
     *
     * @param CPHPCache                    $cacher Кэшер.
     * @param integer                      $ttl Время жизни кэша.
     * @param NamingStrategyInterface|null $namingStrategy Стратегия наименования.
     */
    public function __construct(
        CPHPCache $cacher,
        int $ttl = 0,
        NamingStrategyInterface $namingStrategy = null
    ) {
        $this->cache = $cacher;
        $this->namingStrategy = $namingStrategy ?: new HashNamingStrategy();
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request) : ?ResponseInterface
    {
        if (!$this->checkValidTypeRequest($request)) {
            return null;
        }

        $key = $this->namingStrategy->filename($request);
        $cacheId = md5($key);
        $cachePath = '/'.SITE_ID.'/guzzle-bundle/'.$cacheId;

        $inited = $this->cache->InitCache(
            $this->ttl,
            $key,
            $cachePath
        );

        if ($inited) {
            $vars = $this->cache->GetVars();
            $data = $vars['result'];
            return new Response($data['status'], $data['headers'], $data['body'], $data['version'], $data['reason']);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request, ResponseInterface $response) : void
    {
        if (!$this->checkValidTypeRequest($request)) {
            return;
        }

        $key = $this->namingStrategy->filename($request);
        $cacheId = md5($key);
        $cachePath = '/'.SITE_ID.'/guzzle-bundle/'.$cacheId;

        $inited = $this->cache->InitCache(
            $this->ttl,
            $key,
            $cachePath
        );

        if (!$inited) {
            if ($this->cache->StartDataCache()) {
                $this->cache->EndDataCache(['result' => [
                    'status' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'body' => (string) $response->getBody(),
                    'version' => $response->getProtocolVersion(),
                    'reason' => $response->getReasonPhrase(),
                ]]);
            }

            $response->getBody()->seek(0);
        }
    }

    /**
     * PUT и POST запросы исключить из кэширования.
     *
     * @param RequestInterface $request
     *
     * @return boolean
     *
     * @since 02.12.2020
     */
    private function checkValidTypeRequest(RequestInterface $request) : bool
    {
        if ($request->getMethod() === 'PUT' || $request->getMethod() === 'DELETE') {
            return false;
        }

        return true;
    }
}
