<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;

/**
 * Class SentenceGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class SentenceGenerator implements FixtureGeneratorInterface
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
        return $this->faker->sentence();
    }
}
