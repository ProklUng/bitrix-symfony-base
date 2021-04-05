<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle\Middlewares\History;

use Closure;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

/**
 * History Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class HistoryMiddleware
{
    /**
     * @var History $container
     */
    private $container;

    /**
     * HistoryMiddleware constructor.
     *
     * @param History $container
     */
    public function __construct(History $container)
    {
        $this->container = $container;
    }

    /**
     * @param callable $handler
     *
     * @return Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                function ($response) use ($request, $options) {
                    $this->container->mergeInfo($request, [
                        'response' => $response,
                        'error' => null,
                        'options' => $options,
                        'info' => [],
                    ]);

                    return $response;
                },
                function ($reason) use ($request, $options) : RejectedPromise {
                    $this->container->mergeInfo($request, [
                        'response' => null,
                        'error' => $reason,
                        'options' => $options,
                        'info' => [],
                    ]);

                    return new RejectedPromise($reason);
                }
            );
        };
    }
}
