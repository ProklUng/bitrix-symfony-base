<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface StorageAdapterInterface
 * @package Local\Bundles\GuzzleBundle\Middlewares\Cache\Adapter
 */
interface StorageAdapterInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return null|ResponseInterface
     */
    public function fetch(RequestInterface $request): ?ResponseInterface;

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function save(RequestInterface $request, ResponseInterface $response) : void;
}
