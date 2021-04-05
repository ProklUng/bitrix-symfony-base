<?php

namespace Local\Bundles\ApiExceptionBundle\Exception;

use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\HttpExceptionInterface;

/**
 * Class HttpException
 * @package Local\Bundles\ApiExceptionBundle\Exception
 */
class HttpException extends Exception implements HttpExceptionInterface
{
    /**
     * @var integer $statusCode
     */
    protected $statusCode;

    /**
     * @var array $headers
     */
    protected $headers;

    /**
     * Constructor
     *
     * @param integer $statusCode
     * @param integer $code
     * @param string  $message
     * @param array   $headers
     */
    public function __construct(
        $statusCode = 500,
        $code = 0,
        $message = '',
        array $headers = []
    ) {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
        parent::__construct($code, $message);
    }

    /**
     * Set status code.
     *
     * @param integer $statusCode
     *
     * @return self
     */
    public function setStatusCode($statusCode) : self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Get status code.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set headers.
     *
     * @param array $headers
     *
     * @return self
     */
    public function setHeaders(array $headers) : self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get headers.
     *
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }
}
