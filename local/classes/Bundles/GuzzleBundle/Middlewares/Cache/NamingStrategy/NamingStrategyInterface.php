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
 * Interface NamingStrategyInterface
 * @package Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy
 */
interface NamingStrategyInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    public function filename(RequestInterface $request) : string;
}
