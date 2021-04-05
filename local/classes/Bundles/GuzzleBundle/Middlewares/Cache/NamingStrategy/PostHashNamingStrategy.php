<?php

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

/**
 * Class PostHashNamingStrategy
 * @package Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy
 *
 * @since 22.11.2020
 */
class PostHashNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request): string
    {
        return md5(serialize([
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'headers' => $request->getHeaders(),
            'params' => $request->getMethod() !== 'GET ' ? (string)$request->getBody() : $request->getUri()->getQuery(),
        ]));
    }
}
