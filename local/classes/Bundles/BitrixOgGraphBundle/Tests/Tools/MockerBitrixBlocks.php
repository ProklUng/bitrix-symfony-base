<?php

namespace Local\Bundles\BitrixOgGraphBundle\Tests\Tools;

use _CIBElement;
use CIBlockResult;
use Mockery;

/**
 * Class MockerBitrixBlocks
 * Мок битриксовых запросников к базе.
 * @package Local\Bundles\BitrixOgGraphBundle\Tests\Tools
 *
 * @since 17.12.2020
 * @since 20.02.2021 Мок CIBlockSection.
 */
class MockerBitrixBlocks
{
    /**
     * @var string $srcClass Исходный класс.
     */
    private $srcClass;

    /**
     * @var array $fixture Фикстура.
     */
    private $fixture = [];

    /**
     * @var string $retrieverMethod Метод, извлекающий данные из базы.
     */
    private $retrieverMethod = 'GetNext';

    /**
     * MockerBitrixBlocks constructor.
     *
     * @param string $srcClass Исходный класс.
     */
    public function __construct(string $srcClass)
    {
        $this->srcClass = $srcClass;
    }

    /**
     * Синтаксический сахар в виде статического конструктора. Мок GetNext.
     *
     * @param string $srcClass Исходный класс.
     * @param array $fixture Фикстура.
     *
     * @return mixed
     */
    public static function getNext(string $srcClass, array $fixture = [])
    {
        $self = new static($srcClass);
        $self->setRetrieverMethod('GetNext')
            ->setFixture($fixture);

        return $self->mock();
    }

    /**
     * Синтаксический сахар в виде статического конструктора. Мок Fetch.
     *
     * @param string $srcClass Исходный класс.
     * @param array $fixture Фикстура.
     *
     * @return mixed
     */
    public static function fetch(string $srcClass, array $fixture = [])
    {
        $self = new static($srcClass);
        $self->setRetrieverMethod('Fetch')
            ->setFixture($fixture);

        return $self->mock();
    }

    /**
     * Синтаксический сахар в виде статического конструктора. Мок GetNextElement.
     *
     * @param string $srcClass Исходный класс.
     * @param array $fixture Фикстура.
     *
     * @return mixed
     */
    public static function getNextElement(string $srcClass, array $fixture = [])
    {
        $self = new static($srcClass);
        $self->setRetrieverMethod('GetNextElement')
            ->setFixture($fixture);

        return $self->mock();
    }

    /**
     * @param array $fixture
     *
     * @return $this
     */
    public function setFixture(array $fixture): self
    {
        $this->fixture = $fixture;

        return $this;
    }

    /**
     * @param string $retrieverMethod Метод, извлекающий данные из базы.
     *
     * @return $this
     */
    public function setRetrieverMethod(string $retrieverMethod): self
    {
        $this->retrieverMethod = $retrieverMethod;

        return $this;
    }

    /**
     * @return mixed
     */
    public function mock()
    {
        return $this->getMockCIblockElement(
            $this->fixture,
            'GetList',
            $this->retrieverMethod
        );
    }

    /**
     * @return mixed
     */
    public function mockSection()
    {
        return $this->getMockCIblockSection(
            $this->fixture,
            'GetList',
            $this->retrieverMethod
        );
    }

    /**
     * Мок CIBlockElement.
     *
     * @param string $method
     * @param string $methodRetrieveData
     * @param array $fixture
     *
     * @return mixed
     */
    private function getMockCIblockElement(
        array $fixture = [],
        string $method = 'GetList',
        string $methodRetrieveData = 'GetNext'
    ) {
        $resultQuery = $this->getMockCIBlockResult($methodRetrieveData, $fixture);

        return Mockery::mock(
            $this->srcClass)
            ->makePartial()
            ->shouldReceive($method)
            ->andReturn(
                $resultQuery
            )
            ->getMock();
    }

    /**
     * Мок CIBlockSection.
     *
     * @param string $method
     * @param string $methodRetrieveData
     * @param array $fixture
     *
     * @return mixed
     */
    private function getMockCIblockSection(
        array $fixture = [],
        string $method = 'GetList',
        string $methodRetrieveData = 'GetNext'
    ) {
        $resultQuery = $this->getMockCIBlockResult($methodRetrieveData, $fixture);

        return Mockery::mock(
            $this->srcClass)
            ->makePartial()
            ->shouldReceive($method)
            ->andReturn(
                $resultQuery
            )
            ->getMock();
    }

    /**
     * Мок CIBlockResult. Ответ CIBlockElement -> GetList.
     *
     * @param string $method
     * @param array $fixture
     *
     * @return mixed
     */
    private function getMockCIBlockResult(
        string $method = 'GetNext',
        array $fixture = []
    ) {
        if ($method === 'GetNextElement') {
            return $this->getMockCIBElement($fixture);
        }

        static $count = 0;

        /**
         * Возврат столько массивов, сколько есть в фикстуре.
         * Последним должно вернуться false или null, иначе
         * зацикливание.
         */
        $mock = Mockery::mock(
            CIBlockResult::class)
            ->shouldReceive($method)
            ->andReturnUsing(function () use ($fixture, &$count) {
                if ($count >= count($fixture)) {
                    return false;
                }

                $result = !empty($fixture[$count]) ? $fixture[$count] : $fixture;

                $count++;

                return $result;
            });

        $count = 0;

        return $mock->getMock();
    }

    /**
     * Мок ответа CIBlockResult - GetNextElement (GetFields).
     *
     * @param array $fixture
     *
     * @return mixed
     */
    private function getMockCIBElement(array $fixture = [])
    {
        $mock = Mockery::mock(
            _CIBElement::class)
            ->shouldReceive('GetFields')
            ->andReturn($fixture);

        return $mock->getMock();
    }
}
