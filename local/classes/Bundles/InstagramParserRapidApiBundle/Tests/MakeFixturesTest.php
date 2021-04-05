<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Tests;

use Faker\Factory;
use Faker\Generator;
use Local\Bundles\InstagramParserRapidApiBundle\Command\MakeFixtures;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces\RetrieverInstagramDataInterface;
use Local\Bundles\InstagramParserRapidApiBundle\Services\UserInfoRetriever;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class MakeFixturesTest
 * @package Local\Bundles\InstagramParserRapidApiBundle\Tests
 *
 * @since 26.02.2021
 */
class MakeFixturesTest extends TestCase
{

    /**
     * @var MakeFixtures
     */
    private $command;

    /**
     * @var Generator $faker
     */
    private $faker;

    /**
     * @var string[] $return
     */
    private $return = ['test' => 'test'];

    /**
     * @var string[] $returnUser
     */
    private $returnUser = ['id' => '12345678'];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->deleteFixtures();

        Mockery::resetContainer();
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();

        $this->deleteFixtures();
    }

    /**
     * Успешный запуск команды.
     *
     * @return void
     */
    public function testCommand() : void
    {
        $this->command = new MakeFixtures(
            $this->getMockRetrieverInstagramDataInterface($this->return, 1),
            $this->getMockUserInfoRetriever($this->returnUser, 1),
            'test',
            '/local/classes/Bundles/InstagramParserRapidApiBundle/Tests/Fixtures/response.json',
            '/local/classes/Bundles/InstagramParserRapidApiBundle/Tests/Fixtures/user.json'
        );

        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'username' => 'Wouter',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            'Фикстура данных пользователя создана.',
            $output
        );

        $this->assertStringContainsString(
            'Фикстура ответа Инстаграма создана',
            $output
        );

        $this->deleteFixtures();
    }

    /**
     * Мок RetrieverInstagramDataInterface.
     *
     * @param integer $numberCall Сколько раз вызывается.
     * @param mixed   $return     Возвращаемое.
     *
     * @return mixed
     */
    private function getMockRetrieverInstagramDataInterface($return, int $numberCall = 0)
    {
        $mockRetriever = Mockery::mock(RetrieverInstagramDataInterface::class);
        $mockRetriever->shouldReceive('setUseMock')->withArgs([false])->once()->andReturn($mockRetriever);
        $mockRetriever->shouldReceive('setUserId')->once()->andReturn($mockRetriever);

        $mockRetriever = $mockRetriever->shouldReceive('query')->times($numberCall)->andReturn($return);

        return $mockRetriever->getMock();
    }

    /**
     * Мок UserInfoRetriever.
     *
     * @param integer $numberCall Сколько раз вызывается.
     * @param mixed   $return     Возвращаемое.
     *
     * @return mixed
     */
    private function getMockUserInfoRetriever($return, int $numberCall = 0)
    {
        $mockRetriever = Mockery::mock(UserInfoRetriever::class);
        $mockRetriever->shouldReceive('setUseMock')->withArgs([false])->once()->andReturn($mockRetriever);
        $mockRetriever->shouldReceive('setUserName')->once()->andReturn($mockRetriever);

        $mockRetriever = $mockRetriever->shouldReceive('getAllData')->times($numberCall)->andReturn($return);

        return $mockRetriever->getMock();
    }

    /**
     * @return void
     */
    private function deleteFixtures() : void
    {
        @unlink(
            $_SERVER['DOCUMENT_ROOT'] .
            '/local/classes/Bundles/InstagramParserRapidApiBundle/Tests/Fixtures/user.json'
        );

        @unlink(
            $_SERVER['DOCUMENT_ROOT'] .
            '/local/classes/Bundles/InstagramParserRapidApiBundle/Tests/Fixtures/response.json'
        );
    }
}
