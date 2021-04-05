<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

/**
 * Class HashNamingStrategy
 * @package Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy
 */
class HashNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request) : string
    {
        return md5(serialize([
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'headers' => $request->getHeaders(),
        ]));
    }
}
