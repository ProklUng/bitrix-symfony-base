<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction;

use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;

/**
 * Class AbstractGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction
 *
 * @since 10.04.2021
 */
abstract class AbstractGenerator implements FixtureGeneratorInterface
{
    /**
     * @var array $params Дополнительные runtime параметры.
     */
    protected $params = [];

    /**
     * @inheritDoc
     */
    abstract public function generate(?array $payload = null);

    /**
     * @inheritDoc
     */
    public function setParam(array $params): void
    {
        $this->params = $params;
    }
}