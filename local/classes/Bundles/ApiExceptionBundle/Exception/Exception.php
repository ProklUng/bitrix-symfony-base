<?php

namespace Local\Bundles\ApiExceptionBundle\Exception;

use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\ExceptionInterface;

/**
 * Class Exception
 * @package Local\Bundles\ApiExceptionBundle\Exception
 */
class Exception extends \Exception implements ExceptionInterface
{
    private const VARIABLE_REGEX = "/(\{[a-zA-Z0-9\_]+\})/";

    /**
     * Constructor
     *
     * @param integer $code
     * @param string  $message
     */
    public function __construct(
        $code = 0,
        $message = ''
    ) {
        parent::__construct($message, $code);
    }

    /**
     * Set code
     *
     * @param integer $code
     *
     * @return self
     */
    public function setCode($code) : self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return self
     */
    public function setMessage($message) : self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message with variables
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getMessageWithVariables(): string
    {
        $message = $this->message;

        preg_match(self::VARIABLE_REGEX, $message, $variables);

        foreach ($variables as $variable) {
            $variableName = substr($variable, 1, -1);

            if ($this->$variableName === null) {
                throw new \Exception(sprintf(
                    'Variable "%s" for exception "%s" not found',
                    $variableName,
                    get_class($this)
                ), 500);
            }

            if (!is_string($this->$variableName)) {
                $this->$variableName = (string)$this->$variableName;
            }

            $message = str_replace($variable, $this->$variableName, $message);
        }

        return $message;
    }
}
