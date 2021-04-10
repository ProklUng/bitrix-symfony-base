<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;

/**
 * Class CodeGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class CodeGenerator implements FixtureGeneratorInterface
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * HtmlGenerator constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create('ru_Ru');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        $slug = $this->faker->slug(6);
        if (strlen($slug) > 255) {
            $slug = substr($slug, 0, 255);
        }

        return str_replace(' ', '-', $slug);
    }
}
