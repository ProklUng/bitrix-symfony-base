<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;

/**
 * Class IntGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 10.04.2021
 */
class IntGenerator extends AbstractGenerator
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * @var integer $min Минимум.
     */
    private $min;

    /**
     * @var integer $max Максимум.
     */
    private $max;

    /**
     * IntGenerator constructor.
     *
     * @param integer $min Минимум.
     * @param integer $max Максимум.
     */
    public function __construct(
        int $min = 1,
        int $max = 65000
    ) {
        $this->faker = Factory::create('ru_Ru');

        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        return $this->faker->numberBetween($this->min, $this->max);
    }
}
