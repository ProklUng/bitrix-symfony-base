<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
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
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * TextGenerator constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->faker = Factory::create('ru_RU');
        $this->params['options'] = $options;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        $key = mt_rand(0, count($this->params['options']) - 1);

        return $this->params['options'][$key];
    }

}
