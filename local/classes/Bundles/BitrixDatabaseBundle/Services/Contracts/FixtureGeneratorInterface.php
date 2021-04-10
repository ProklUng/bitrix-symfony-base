<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Contracts;

/**
 * Interface FixtureGeneratorInterface
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Contracts
 *
 * @since 08.04.2021
 */
interface FixtureGeneratorInterface
{
    /**
     * Сгенерировать фикстуру для поля.
     *
     * @param array|null $payload Нагрузка.
     *
     * @return mixed
     */
    public function generate(?array $payload = null);

    /**
     * @param array $params Задать дополнительные параметры.
     *
     * @return void
     */
    public function setParam(array $params) : void;
}
