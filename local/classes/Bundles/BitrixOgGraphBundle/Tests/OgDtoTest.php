<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests;

use Faker\Factory;
use Local\Bundles\BitrixOgGraphBundle\Services\OgDTO;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class OgDtoTest
 * @package Local\Bundles\BitrixOgGraphBundle\Tests
 *
 * @since 21.02.2021
 */
class OgDtoTest extends TestCase
{
    /**
     * @var ogDTO $obTestObject
     */
    private $obTestObject;

    /**
     * @var array $fixture
     */
    private $fixture;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Mockery::resetContainer();
        parent::setUp();

        $faker = Factory::create();
        $this->fixture = [
            'url' => $faker->url,
            'title' => $faker->sentence,
            'img' => $faker->url,
            'description' => $faker->sentence(22),
            'site_name' => $faker->sentence(2),
            'type' => 'website',
            'timePublished' => $faker->date(),
            'fb_admins' => $faker->numerify(),
            'article_publisher' => $faker->name(),
        ];

        $this->obTestObject = new ogDTO([]);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * update().
     *
     * @return void
     */
    public function testUpdate() : void
    {
        $this->obTestObject->update($this->fixture);

        $result = $this->obTestObject->toArray();
        foreach ($this->fixture as $key => $item) {
            $this->assertSame(
                $item,
                $result[$key],
                'Ключ ' . $key . ' обработан неправильно.'
            );
        }
    }
}