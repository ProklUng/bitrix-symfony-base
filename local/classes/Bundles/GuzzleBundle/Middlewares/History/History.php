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

use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;

/**
 * Class History
 * @package Local\Bundles\GuzzleBundle\Middlewares\History
 */
class History extends \SplObjectStorage
{
    /**
     * @param RequestInterface $request
     * @param array            $info
     *
     * @return void
     */
    public function mergeInfo(RequestInterface $request, array $info): void
    {
        $info = array_merge(
            ['response' => null, 'error' => null, 'info' => null],
            array_filter($this->contains($request) ? $this[$request] : []),
            array_filter($info)
        );

        $this->attach($request, $info);
    }

    /**
     * @param TransferStats $stats
     *
     * @return void
     */
    public function addStats(TransferStats $stats): void
    {
        $this->mergeInfo($stats->getRequest(), ['info' => $stats->getHandlerStats()]);
    }
}
