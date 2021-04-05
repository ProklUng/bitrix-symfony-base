<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Tests;

use Exception;
use Local\Bundles\InstagramParserRapidApiBundle\Services\ComplexParser;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces\InstagramDataTransformerInterface;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces\RetrieverInstagramDataInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class ComplexParserTest
 * @package Local\Bundles\InstagramParserRapidApiBundle\Tests
 *
 * @since 22.02.2021
 */
class ComplexParserTest extends TestCase
{
    /**
     * @var ComplexParser
     */
    private $testObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Mockery::resetContainer();
        parent::setUp();
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
     * parse(). Количество вызовов.
     *
     * @return void
     * @throws Exception
     */
    public function testParse() : void
    {
        $mockRetriever = Mockery::mock(RetrieverInstagramDataInterface::class);
        $mockRetriever->shouldReceive('query')->once()->andReturn([]);
        $mockRetriever = $mockRetriever->shouldReceive('setAfterMark')->never();

        $mockTransformer = Mockery::mock(InstagramDataTransformerInterface::class);
        $mockTransformer = $mockTransformer->shouldReceive('processMedias')->once()->andReturn([]);

        $this->testObject = new ComplexParser(
            $mockRetriever->getMock(),
            $mockTransformer->getMock()
        );

        $this->testObject->parse();

        $this->assertTrue(true);
    }

    /**
     * parse(). Количество вызовов. Обработка параметра after.
     *
     * @return void
     * @throws Exception
     */
    public function testParseAfterParam() : void
    {
        $mockRetriever = Mockery::mock(RetrieverInstagramDataInterface::class);
        $mockRetriever->shouldReceive('query')->once()->andReturn([]);
        $mockRetriever = $mockRetriever->shouldReceive('setAfterMark')->once();

        $mockTransformer = Mockery::mock(InstagramDataTransformerInterface::class);
        $mockTransformer = $mockTransformer->shouldReceive('processMedias')->once()->andReturn([]);

        $this->testObject = new ComplexParser(
            $mockRetriever->getMock(),
            $mockTransformer->getMock()
        );
        $this->testObject->setAfterParam('xxxxxxxxxxxx');

        $this->testObject->parse();

        $this->assertTrue(true);
    }
}
