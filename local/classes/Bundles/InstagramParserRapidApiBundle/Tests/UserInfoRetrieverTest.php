<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Tests;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Exceptions\InstagramTransportException;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Transport\InstagramTransportInterface;
use Local\Bundles\InstagramParserRapidApiBundle\Services\UserInfoRetriever;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class UserInfoRetrieverTest
 * @package Local\Bundles\InstagramParserRapidApiBundle\Tests
 *
 * @since 23.02.2021
 */
class UserInfoRetrieverTest extends TestCase
{
    private const FIXTURE_PATH = '/local/classes/Bundles/InstagramParserRapidApiBundle/Tests/Fixtures/fixture.txt';

    /**
     * @var UserInfoRetriever
     */
    private $testObject;

    /**
     * @var Generator $faker
     */
    private $faker;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {

        @unlink($_SERVER['DOCUMENT_ROOT'] . self::FIXTURE_PATH);
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

        @unlink($_SERVER['DOCUMENT_ROOT'] . self::FIXTURE_PATH);
    }

    /**
     * getUserId(). Количество вызовов кэшера.
     *
     * @return void
     * @throws Exception
     */
    public function testGetUserIdFromCache() : void
    {
        $fakeId = (string)$this->faker->numberBetween(10000, 20000);
        $return = ['id' => $fakeId];

        $this->testObject = new UserInfoRetriever(
            $this->getMockCacher($return, 1),
            $this->getMockInstagramTransport($return, 0),
        );

        $this->testObject->setUserName($this->faker->sentence(1));

        $result = $this->testObject->getUserId();

        $this->assertSame(
            $fakeId,
            $result,
            'Неправильный результат.'
        );
    }

    /**
     * getAllData(). Очистка ключей. Мок.
     *
     * @return void
     * @throws Exception
     */
    public function testGetAllDataClearingArrayMock() : void
    {
        $keys = [
            'edge_felix_video_timeline',
            'edge_owner_to_timeline_media',
            'edge_related_profiles',
            'edge_media_collections',
            'edge_saved_media'
        ];

        $this->testObject = new UserInfoRetriever(
            $this->getMockCacher([], 0),
            $this->getMockInstagramTransport([], 0),
        );

        $this->testObject->setUseMock(true, self::FIXTURE_PATH);
        $this->testObject->setUserName($this->faker->sentence(1));

        $result = $this->testObject->getAllData();

        foreach ($keys as $data) {
            $this->assertArrayNotHasKey($data, $result);
        }
    }

    /**
     * getUserId(). Кэширование.
     *
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetUserCaching() : void
    {
        $fakeId = (string)$this->faker->numberBetween(10000, 20000);
        $return = json_encode(['id' => $fakeId]);

        $cacher = new ArrayAdapter(0);

        $this->testObject = new UserInfoRetriever(
            $cacher,
            $this->getMockInstagramTransport($return, 1),
        );

        $this->testObject->setUserName('testing');

        // Прогрев кэша.
        $result = $this->testObject->getUserId();

        $this->assertSame(
            $fakeId,
            $result,
            'Неправильный результат.'
        );

        $this->testObject = new UserInfoRetriever(
            $cacher,
            $this->getMockInstagramTransport($return, 0),
        );

        $this->testObject->setUserName('testing');

        // Должны получить из кэша. Загрузчик Инстаграма вызываться
        // не должен.
        $this->testObject->getUserId();

        $this->assertSame(
            $fakeId,
            $result,
            'Неправильный результат.'
        );
    }

    /**
     * getUserId(). Сработает ли фикстура.
     *
     * @return void
     * @throws Exception
     */
    public function testGetUserIdFromMock() : void
    {
        $fakeId = (string)$this->faker->numberBetween(10000, 20000);
        $return = ['id' => $fakeId];

        $this->testObject = new UserInfoRetriever(
            $this->getMockCacher($return, 0),
            $this->getMockInstagramTransport(json_encode($return), 0)
        );

        $this->createFixture(json_encode(['id' => $fakeId]));

        $this->testObject->setUseMock(true, self::FIXTURE_PATH);
        $this->testObject->setUserName($this->faker->sentence(1));

        $result = $this->testObject->getUserId();

        $this->assertSame(
            $fakeId,
            $result,
            'Неправильный результат.'
        );
    }

    /**
     * getUserId(). Ошибки API.
     *
     * @return void
     * @throws Exception
     */
    public function testGetUserIdErrorsApi() : void
    {
        $return = ['id' => (string)$this->faker->numberBetween(10000, 20000)];
        $errorMessage = 'Test error message';

        $this->testObject = new UserInfoRetriever(
            $this->getMockCacherWithDelete([
                'message' => $errorMessage
            ]),
            $this->getMockInstagramTransport(json_encode($return), 0),
        );

        $this->testObject->setUserName($this->faker->sentence(1));

        $this->expectException(InstagramTransportException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(400);

        $this->testObject->getUserId();
    }

    /**
     * getUserId(). Ошибки транспорта.
     *
     * @return void
     * @throws Exception
     */
    public function testGetUserIdErrorsTransport() : void
    {
        $errorMessage = 'Get Request Error: answer not json!';

        $this->testObject = new UserInfoRetriever(
            $this->getMockCacherWithDelete([
                'message' => $errorMessage
            ]),
            $this->getMockInstagramTransport(null, 0),
        );

        $this->testObject->setUserName($this->faker->sentence(1));

        $this->expectException(InstagramTransportException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(400);

        $this->testObject->getUserId();
    }

    /**
     * getUserId(). Exceptions транспорта.
     *
     * @return void
     * @throws Exception
     */
    public function testGetUserIdExceptionsTransport() : void
    {
        $mockRetriever = Mockery::mock(InstagramTransportInterface::class);
        $mockRetriever = $mockRetriever->shouldReceive('get')->times(1)->andThrow(
            Exception::class
        );

        $this->testObject = new UserInfoRetriever(
            new ArrayAdapter(0),
            $mockRetriever->getMock(),
        );

        $this->testObject->setUserName($this->faker->sentence(1));

        $this->expectException(InstagramTransportException::class);
        $this->expectExceptionMessage('Get Request Error: answer not json');
        $this->expectExceptionCode(400);

        $this->testObject->getUserId();
    }

    /**
     * Мок загрузчика Инстаграма.
     *
     * @param integer $numberCall Сколько раз вызывается.
     * @param mixed   $return     Возвращаемое.
     *
     * @return mixed
     */
    private function getMockInstagramTransport($return, int $numberCall = 0)
    {
        $mockRetriever = Mockery::mock(InstagramTransportInterface::class);
        $mockRetriever = $mockRetriever->shouldReceive('get')->times($numberCall)->andReturn($return);

        return $mockRetriever->getMock();
    }

    /**
     * Мок кэшера.
     *
     * @param integer $numberCall Сколько раз вызывается.
     * @param mixed   $return     Возвращаемое.
     *
     * @return mixed
     */
    private function getMockCacher($return, int $numberCall = 0)
    {
        $mockCacher = Mockery::mock(CacheInterface::class);
        $mockCacher = $mockCacher->shouldReceive('get')->times($numberCall)->andReturn($return);

        return $mockCacher->getMock();
    }

    /**
     * Мок кэшера с вызовом delete.
     *
     * @param integer $numberCall Сколько раз вызывается.
     * @param mixed   $return     Возвращаемое.
     *
     * @return mixed
     */
    private function getMockCacherWithDelete($return)
    {
        $mockCacher = Mockery::mock(CacheInterface::class);
        $mockCacher->shouldReceive('delete')->once();
        $mockCacher = $mockCacher->shouldReceive('get')->once()->andReturn($return);

        return $mockCacher->getMock();
    }

    /**
     * Создать фикстуру.
     *
     * @param string $value
     * @return void
     */
    private function createFixture(string $value) : void
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . self::FIXTURE_PATH,
            $value
        );
    }
}
