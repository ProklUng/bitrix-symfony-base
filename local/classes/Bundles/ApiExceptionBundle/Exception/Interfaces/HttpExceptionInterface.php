<?php

namespace Local\Bundles\ApiExceptionBundle\Exception\Interfaces;

/**
 * Interface HttpExceptionInterface
 */
interface HttpExceptionInterface
{
    /**
     * Set status code.
     *
     * @param integer $statusCode
     *
     * @return self
     */
    public function setStatusCode($statusCode) : self;

    /**
     * Get status code.
     *
     * @return mixed
     */
    public function getStatusCode();

    /**
     * Set headers.
     *
     * @param array $headers
     *
     * @return self
     */
    public function setHeaders(array $headers) : self;

    /**
     * Get headers.
     *
     * @return array
     */
    public function getHeaders() : array;
}
