<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests;

use Bitrix\Main\Application;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixOgGraphBundle\Services\OgDTO;
use Local\Bundles\BitrixOgGraphBundle\Services\StaticPageProcessor;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use WebArch\BitrixCache\AntiStampedeCacheAdapter;

/**
 * Class StaticPageProcessorTest
 * @package Local\Bundles\BitrixOgGraphBundle\Tests
 * @coversDefaultClass StaticPageProcessor
 *
 * @since 20.02.20201
 */
class StaticPageProcessorTest extends TestCase
{
    /**
     * @var StaticPageProcessor $obTestObject
     */
    protected $obTestObject;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var OgDTO $dtoOpenGraph DTO для теста.
     */
    private $dtoOpenGraph;

    protected function setUp(): void
    {
        Mockery::resetContainer();
        parent::setUp();
        $this->faker = Factory::create();

        $this->dtoOpenGraph = new ogDTO([]);
        $this->obTestObject = new StaticPageProcessor(
            $_SERVER['DOCUMENT_ROOT'],
            Application::getInstance(),
            new AntiStampedeCacheAdapter(
                '/', 0, '/cache/s1/test/'
            )
        );
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
     * go().
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGo(): void
    {
        $GLOBALS['APPLICATION']->SetPageProperty('title', 'test title');
        $GLOBALS['APPLICATION']->SetPageProperty('description', 'test description');

        $result = $this->obTestObject->go();

        $this->assertEmpty(
            $result['timePublished'],
            'Левый timePublished.'
        );

        $this->assertSame(
            'test title',
            $result['title'],
            'Не обработан title.'
        );

        $this->assertSame(
            'test description',
            $result['description'],
            'Не обработан description.'
        );

        $this->assertSame(
            'website',
            $result['type'],
            'Не обработан type.'
        );
    }

    /**
     * go(). Проверка обработки пустых значений.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGoEmptyValues(): void
    {
        $GLOBALS['APPLICATION']->SetPageProperty('title', '');
        $GLOBALS['APPLICATION']->SetPageProperty('description', '');

        $result = $this->obTestObject->go();

        $this->assertSame(
            '',
            $result['title'],
            'Обработан пустой title.'
        );

        $this->assertSame(
            '',
            $result['description'],
            'Обработан пустой description.'
        );

        $this->assertSame(
            'website',
            $result['type'],
            'Обнулился type.'
        );
    }
}
