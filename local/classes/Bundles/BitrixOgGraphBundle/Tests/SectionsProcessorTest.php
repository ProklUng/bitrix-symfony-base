<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests;

use CFile;
use CIBlockSection;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixOgGraphBundle\Services\SectionsProcessor;
use Local\Bundles\BitrixOgGraphBundle\Services\Utils\CFileWrapper;
use Local\Bundles\BitrixOgGraphBundle\Tests\Tools\MockerBitrixBlocks;
use Local\Bundles\BitrixOgGraphBundle\Tests\Tools\MockerBitrixSeo;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use WebArch\BitrixCache\AntiStampedeCacheAdapter;

/**
 * Class SectionsProcessorTest
 * @package Local\Bundles\BitrixOgGraphBundle\Tests
 * @coversDefaultClass SectionsProcessor
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @since 20.02.20201
 */
class SectionsProcessorTest extends TestCase
{
    /**
     * @var SectionsProcessor $obTestObject
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

        $mockCIBlockSection = new MockerBitrixBlocks(CIBlockSection::class);
        $mockCIBlockSection->setFixture([
            'ID' => 22,
            'NAME' => 'test name',
            'DESCRIPTION' => 'test description',
            'PICTURE' => null,
            'TIMESTAMP_X' => '',
            'SECTION_PAGE_URL' => '/test/url/',
        ]);

        $this->obTestObject = new SectionsProcessor(
            $mockCIBlockSection->mockSection(),
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
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGo(): void
    {
        $result = $this->obTestObject->go();

        $this->assertSame('test name', $result['title'], 'Title не обработан.');
        $this->assertSame('test description', $result['description'], 'Description не обработан.');
        $this->assertSame('website', $result['type'], 'Type не обработан.');
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
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGoFromSeoProperty(): void
    {
        $mockerBitrixSeo = new MockerBitrixSeo(
            [
                'SECTION_META_TITLE' => [
                    'VALUE' => 'test SEO title',
                ],
                'SECTION_META_DESCRIPTION' => [
                    'VALUE' => 'test SEO description',
                ],
            ]
        );

        $mockerBitrixSeo->mockSectionValues();

        $result = $this->obTestObject->go();

        $this->assertSame('test SEO title', $result['title'], 'Title не обработан.');
        $this->assertSame('test SEO description', $result['description'], 'Description не обработан.');
        $this->assertSame('website', $result['type'], 'Type не обработан.');
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
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGoCutMaxLength(): void
    {
        $mockerBitrixSeo = new MockerBitrixSeo(
            [
                'SECTION_META_DESCRIPTION' => [
                    'VALUE' => $this->faker->text(400),
                ],
            ]
        );

        $mockerBitrixSeo->mockSectionValues();

        $result = $this->obTestObject->go();

        $this->assertSame(
            200,
            strlen($result['description']),
            'Обрезка текста description не состоялась.'
        );
    }
}
