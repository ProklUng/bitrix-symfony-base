<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Bitrix\Main\Type\DateTime;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;

/**
 * Class DateGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class DateGenerator implements FixtureGeneratorInterface
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
     */
    public function generate(?array $payload = null)
    {
        return DateTime::createFromUserTime(
            $this->faker->dateTimeThisYear->format('d.m.Y H:i:s')
        );
    }
}
