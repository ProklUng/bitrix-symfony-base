<?php

namespace Local\Bundles\ApiExceptionBundle\Manager;

use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\HttpExceptionInterface;
use Throwable;
use Local\Bundles\ApiExceptionBundle\Exception\Interfaces\ExceptionInterface;

/**
 * Manage exceptions to define public data returned
 * @package Local\Bundles\ApiExceptionBundle\Manager
 */
class ExceptionManager
{
    /**
     * @var array $defaultConfig Конфигурация по-умолчанию.
     */
    protected $defaultConfig;

    /**
     * @var array $exceptions Exceptions.
     */
    protected $exceptions;

    /**
     * Constructor.
     *
     * @param array $defaultConfig Конфигурация по-умолчанию.
     * @param array $exceptions    Exceptions.
     */
    public function __construct(array $defaultConfig, array $exceptions)
    {
        $this->defaultConfig    = $defaultConfig;
        $this->exceptions       = $exceptions;
    }

    /**
     * Configure Exception
     *
     * @param Throwable $exception Exception.
     *
     * @return Throwable
     */
    public function configure(Throwable $exception): Throwable
    {
        $exceptionName = get_class($exception);

        $configException = $this->getConfigException($exceptionName);

        // @phpstan-ignore-next-line
        $exception->setCode($configException['code']);
        // @phpstan-ignore-next-line
        $exception->setMessage($configException['message']);

        if ($exception instanceof HttpExceptionInterface) {
            $exception->setStatusCode($configException['status']);
            $exception->setHeaders($configException['headers']);
        }

        return $exception;
    }

    /**
     * Get config to exception
     *
     * @param string $exceptionName Название исключения.
     *
     * @return array
     */
    protected function getConfigException($exceptionName): array
    {
        $exceptionParentName = get_parent_class($exceptionName);

        if (in_array(ExceptionInterface::class,
            class_implements($exceptionParentName), true)) {
            $parentConfig = $this->exceptions[$exceptionName] ?? $this->defaultConfig;
        } else {
            $parentConfig = $this->defaultConfig;
        }

        if (array_key_exists($exceptionName, $this->exceptions)
            &&
            $this->exceptions[$exceptionName]
        ) {
            return array_merge($parentConfig, $this->exceptions[$exceptionName]);
        }

        return $parentConfig;
    }
}
