<?php

namespace Local\Bundles\ApiExceptionBundle\Exception\Interfaces;

/**
 * Interface ExceptionInterface
 *
 * @package Local\Bundles\ApiExceptionBundle\Exception\Interfaces
 */
interface ExceptionInterface
{
    /**
     * Set code.
     *
     * @param integer $code
     *
     * @return self
     */
    public function setCode($code) : self;

    /**
     * Get code
     *
     * @return mixed
     */
    public function getCode();

    /**
     * Set Message
     *
     * @param string $message
     *
     * @return self
     */
    public function setMessage($message) : self;

    /**
     * Get message
     *
     * @return mixed
     */
    public function getMessage();

    /**
     * Get message with variables.
     *
     * @return string
     */
    public function getMessageWithVariables() : string;
}
