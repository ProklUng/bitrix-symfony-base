<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests\Tools;

use Mockery;

/**
 * Class MockerBitrixSeo
 * Мокирование SectionValues & ElementValues.
 * @package Local\Bundles\BitrixOgGraphBundle\Tests\Tools
 *
 * @since 20.02.2021
 */
class MockerBitrixSeo
{
    /**
     * @var array $fixture Фикстура.
     */
    private $fixture;

    /**
     * MockerBitrixSeo constructor.
     *
     * @param array $fixture Фикстура.
     */
    public function __construct(array $fixture = [])
    {
        $this->fixture = $fixture;
    }

    /**
     * Мок SectionValues.
     *
     * @return void
     */
    public function mockSectionValues() : void
    {
        $mock = Mockery::mock('overload:Bitrix\Iblock\InheritedProperty\SectionValues');

        $mock->shouldReceive('queryValues')->andReturn($this->fixture);
        $mock->shouldReceive('clearValues');
    }

    /**
     * Мок ElementValues.
     *
     * @return void
     */
    public function mockElementValues() : void
    {
        $mock = Mockery::mock('overload:Bitrix\Iblock\InheritedProperty\ElementValues');

        $mock->shouldReceive('queryValues')->andReturn($this->fixture);
        $mock->shouldReceive('clearValues');
    }
}
