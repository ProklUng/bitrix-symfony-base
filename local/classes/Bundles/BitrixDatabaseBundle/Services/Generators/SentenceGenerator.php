<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;

/**
 * Class SentenceGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class SentenceGenerator extends AbstractGenerator
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * SentenceGenerator constructor.
     *
     */
    public function __construct()
    {
        $this->faker = Factory::create('ru_RU');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        if (array_key_exists('words', $this->params)) {
            return $this->faker->sentence((int)$this->params['words']);
        }

        $this->params = [];

        return $this->faker->sentence();
    }
}
