<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;

/**
 * Class BaseOptionGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 11.04.2021
 */
class BaseOptionGenerator extends AbstractGenerator
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        if (!array_key_exists('options', $payload['params'])) {
            return null;
        }

        $key = random_int(0, count($payload['params']['options']) - 1);

        return $payload['params']['options'][$key];
    }

}
