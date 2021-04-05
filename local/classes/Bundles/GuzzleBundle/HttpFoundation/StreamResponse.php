<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle\HttpFoundation;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class StreamResponse extends Response
{
    /**
     * @const BUFFER_SIZE
     */
    public const BUFFER_SIZE = 4096;

    /**
     * @var int|mixed $bufferSize
     */
    private $bufferSize;

    /**
     * StreamResponse constructor.
     *
     * @param ResponseInterface $response
     * @param integer           $bufferSize
     */
    public function __construct(ResponseInterface $response, $bufferSize = self::BUFFER_SIZE)
    {
        parent::__construct(null, $response->getStatusCode(), $response->getHeaders());

        $this->content = $response->getBody();
        $this->bufferSize = $bufferSize;
    }

    /**
     * @return $this
     */
    public function sendContent() : self
    {
        $chunked = $this->headers->has('Transfer-Encoding');
        $this->content->seek(0);

        for (;;) {
            $chunk = $this->content->read($this->bufferSize);

            if ($chunked) {
                echo sprintf("%x\r\n", strlen($chunk));
            }

            echo $chunk;

            if ($chunked) {
                echo "\r\n";
            }

            flush();

            if (!$chunk) {
                return $this;
            }
        }

        return $this;
    }

    /**
     * @return string|false
     */
    public function getContent()
    {
        return false;
    }
}
