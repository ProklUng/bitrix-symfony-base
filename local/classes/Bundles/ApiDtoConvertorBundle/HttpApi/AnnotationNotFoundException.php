<?php

declare(strict_types=1);

namespace Local\Bundles\ApiDtoConvertorBundle\HttpApi;

use RuntimeException;
use Throwable;

class AnnotationNotFoundException extends RuntimeException implements Throwable
{
    /**
     * AnnotationNotFoundException constructor.
     *
     * @param string $message
     */
    private function __construct(string $message)
    {
        $this->message = $message;
        parent::__construct();
    }

    /**
     * @param string $className
     * @return static
     */
    public static function httpApi(string $className): self
    {
        return new self(sprintf('Annotation \'@HttpApi\' for %s not found.', $className));
    }
}
