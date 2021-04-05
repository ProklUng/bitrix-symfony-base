<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests;

use Local\Bundles\BitrixOgGraphBundle\Services\DetailPageProcessor;
use Local\Bundles\BitrixOgGraphBundle\Services\Facades\FacadeOgGraphDetailPage;
use Local\Bundles\BitrixOgGraphBundle\Services\InjectGraph;
use Local\Bundles\BitrixOgGraphBundle\Services\OgDTO;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

/**
 * Class FacadeOgGraphDetailPageTest
 * @package Local\Bundles\BitrixOgGraphBundle\Tests
 *
 * @since 20.02.2021
 */
class FacadeOgGraphDetailPageTest extends TestCase
{
    /**
     * @var FacadeOgGraphDetailPage $obTestObject
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
        $this->obTestObject = new FacadeOgGraphDetailPage(
            $this->getMockDetailPageProcessor(),
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
        $this->obTestObject->make(1, 1);

        $this->assertTrue(true);
    }

    /**
     * Мок DetailPageProcessor.
     *
     * @return mixed
     */
    private function getMockDetailPageProcessor()
    {
        $mock = Mockery::mock(DetailPageProcessor::class)->makePartial();

        $mock->shouldReceive('setIblockId')->once()->andReturnSelf();
        $mock->shouldReceive('setIdElement')->once()->andReturnSelf();
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
