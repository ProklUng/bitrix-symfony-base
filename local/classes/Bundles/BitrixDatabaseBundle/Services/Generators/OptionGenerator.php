<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;

/**
 * Class OptionGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 10.04.2021
 */
class OptionGenerator extends AbstractGenerator
{
    /**
     * OptionGenerator constructor.
     *
     * @param array $options Варианты.
     */
    public function __construct(array $options = [])
    {
        $this->params['options'] = $options;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        $key = random_int(0, count($this->params['options']) - 1);

        return $this->params['options'][$key];
    }

}
