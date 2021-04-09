<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;

/**
 * Class HtmlGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class HtmlGenerator implements FixtureGeneratorInterface
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
        $paragraphs = random_int(5, 15);
        $i = 0;
        $result = '';

        while ($i < $paragraphs) {
            $result .= '<p>' . $this->faker->paragraph(random_int(2, 6)) . '</p>';
            $i++;
        }

        return $result;
    }
}
