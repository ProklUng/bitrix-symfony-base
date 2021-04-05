<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests;

use Local\Bundles\BitrixOgGraphBundle\Services\Facades\FacadeOgGraphStatic;
use Local\Bundles\BitrixOgGraphBundle\Services\InjectGraph;
use Local\Bundles\BitrixOgGraphBundle\Services\OgDTO;
use Local\Bundles\BitrixOgGraphBundle\Services\StaticPageProcessor;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

/**
 * Class FacadeOgGraphDetailPageTest
 * @package Local\Bundles\BitrixOgGraphBundle\Tests
 *
 * @since 21.02.2021
 */
class FacadeOgGraphStaticTest extends TestCase
{
    /**
     * @var FacadeOgGraphStatic $obTestObject
     */
    protected $obTestObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Mockery::resetContainer();
        parent::setUp();
        $dtoOpenGraph = new ogDTO([]);
        $this->obTestObject = new FacadeOgGraphStatic(
            $this->getMockStaticPageProcessor(),
            $this->getMockInjectGraph(),
            $dtoOpenGraph
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
     * Проверка на количество вызываемых методов.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testMake() : void
    {
        $this->obTestObject->make();

        $this->assertTrue(true);
    }

    /**
     * Мок StaticPageProcessor.
     *
     * @return mixed
     */
    private function getMockStaticPageProcessor()
    {
        $mock = Mockery::mock(StaticPageProcessor::class)->makePartial();
        $mock = $mock->shouldReceive('go')->once()->andReturn(['test']);

        return $mock->getMock();
    }

    /**
     * Мок InjectGraph.
     *
     * @return mixed
     */
    private function getMockInjectGraph()
    {
        $mock = Mockery::mock(InjectGraph::class);
        $mock = $mock->shouldReceive('inject')->once();

        return $mock->getMock();
    }
}
