<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests;

use CFile;
use CIBlockElement;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixOgGraphBundle\Services\DetailPageProcessor;
use Local\Bundles\BitrixOgGraphBundle\Services\Utils\CFileWrapper;
use Local\Bundles\BitrixOgGraphBundle\Tests\Tools\MockerBitrixBlocks;
use Local\Bundles\BitrixOgGraphBundle\Tests\Tools\MockerBitrixSeo;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use WebArch\BitrixCache\AntiStampedeCacheAdapter;

/**
 * Class DetailPageProcessorTest
 * @package Local\Bundles\BitrixOgGraphBundle\Tests
 * @coversDefaultClass DetailPageProcessor
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @since 20.02.20201
 */
class DetailPageProcessorTest extends TestCase
{
    /**
     * @var DetailPageProcessor $obTestObject
     */
    private $obTestObject;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Mockery::resetContainer();
        parent::setUp();
        $this->faker = Factory::create();

        $mockCIBlockElement = new MockerBitrixBlocks(CIBlockElement::class);
        $mockCIBlockElement->setFixture([
            [
                'ID' => 22,
                'NAME' => 'test name',
                'PREVIEW_TEXT' => 'test description',
                'PREVIEW_PICTURE' => null,
                'TIMESTAMP_X' => '',
                'DETAIL_PAGE_URL' => '/test/url/',
            ],
        ]);

        $this->obTestObject = new DetailPageProcessor(
            $mockCIBlockElement->mock(),
            new CFileWrapper(new CFile()),
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
     * go(). Default values.
     *
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGo(): void
    {
        $result = $this->obTestObject->go();

        $this->assertSame('test name', $result['title'], 'Title не обработан.');
        $this->assertSame('test description', $result['description'], 'Description не обработан.');
        $this->assertSame('article', $result['type'], 'Type не обработан.');
        $this->assertEmpty($result['img'], 'Почему-то обработалась картинка.');

        $this->assertStringContainsString(
            '/test/url/',
            $result['url'],
            'URL не обработан.'
        );

    }

    /**
     * go(). SEO properties.
     *
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGoFromSeoProperty(): void
    {
        $mockerBitrixSeo = new MockerBitrixSeo(
            [
                'ELEMENT_META_TITLE' => [
                    'VALUE' => 'test SEO title',
                ],
                'ELEMENT_META_DESCRIPTION' => [
                    'VALUE' => 'test SEO description',
                ],
            ]
        );

        $mockerBitrixSeo->mockElementValues();

        $result = $this->obTestObject->go();

        $this->assertSame('test SEO title', $result['title'], 'Title не обработан.');
        $this->assertSame('test SEO description', $result['description'], 'Description не обработан.');
        $this->assertSame('article', $result['type'], 'Type не обработан.');
        $this->assertEmpty($result['img'], 'Почему-то обработалась картинка.');

        $this->assertStringContainsString(
            '/test/url/',
            $result['url'],
            'URL не обработан.'
        );
    }

    /**
     * Как обрабатывается длина description.
     *
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGoCutMaxLength(): void
    {
        $mockerBitrixSeo = new MockerBitrixSeo(
            [
                'ELEMENT_META_DESCRIPTION' => [
                    'VALUE' => $this->faker->text(400),
                ],
            ]
        );

        $mockerBitrixSeo->mockElementValues();

        $result = $this->obTestObject->go();

        $this->assertSame(
            200,
            strlen($result['description']),
            'Обрезка текста description не состоялась.'
        );
    }
}
