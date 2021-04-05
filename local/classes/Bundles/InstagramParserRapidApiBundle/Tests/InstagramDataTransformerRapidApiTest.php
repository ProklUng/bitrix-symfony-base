<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Tests;

use Local\Bundles\InstagramParserRapidApiBundle\Services\InstagramDataTransformerRapidApi;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class InstagramDataTransformerRapidApiTest
 * @package Local\Bundles\InstagramParserRapidApiBundle\Tests
 *
 * @since 22.02.2021
 */
class InstagramDataTransformerRapidApiTest extends TestCase
{
    /**
     * @var InstagramDataTransformerRapidApi
     */
    private $testObject;

    /**
     * @var array $validFixture
     */
    private $validFixture;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Mockery::resetContainer();
        parent::setUp();

        $this->validFixture = json_decode(file_get_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/response.txt'
        ), true);

        $this->testObject = new InstagramDataTransformerRapidApi();
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
     * processMedias(). Валидная фикстура.
     *
     * @return void
     */
    public function testProcessMediasValidData() : void
    {
        $validFixture = json_decode(file_get_contents(
          $_SERVER['DOCUMENT_ROOT'] . '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/response.txt'
        ), true);

        $result = $this->testObject->processMedias($validFixture);

        $this->assertCount(3, $result, 'Количество картинок по умолчанию не обработано');

        foreach ($result as $item) {
            $this->assertNotEmpty(
                $item['link'],
                'Ссылка на пост не обработана.'
            );

            $this->assertStringContainsString(
                'https://www.instagram.com/p/',
                $item['link'],
                'Ссылка на пост обработана неправильно.'
            );

            $this->assertNotEmpty(
                $item['description'],
                'Description не обработан.'
            );
        }
    }

    /**
     * getNextPageCursor(). Валидная фикстура.
     *
     * @return void
     */
    public function testProcessCursor() : void
    {
        $validFixture = json_decode(file_get_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/response.txt'
        ), true);

        $result = $this->testObject->getNextPageCursor($validFixture);

        $this->assertNotEmpty(
            $result,
            'Курсор следующей страницы не определился.'
        );
    }

    /**
     * getNextPageCursor(). Нет следующей страницы.
     *
     * @return void
     */
    public function testProcessCursorNoNextPage() : void
    {
        $validFixture = json_decode(file_get_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/response.txt'
        ), true);

        $validFixture['page_info']['has_next_page'] = false;

        $result = $this->testObject->getNextPageCursor($validFixture);

        $this->assertEmpty(
            $result,
            'Курсор следующей страницы определился, а не должен.'
        );
    }

    /**
     * processMedias(). Игнор видео.
     *
     * @return void
     */
    public function testProcessMediasIgnoreVideo() : void
    {
        $validFixture = [
            'edges' => [
                'is_video' => true,
                'shortcode' => 'shortcode',
                'image' => 'http://image.image/image.jpg',
                'description' => 'description'
            ]
        ];

        $result = $this->testObject->processMedias($validFixture);

        $this->assertCount(0, $result, 'Игнор видео не обработан.');
    }

    /**
     * processMedias(). Невалидная фикстура.
     *
     * @param array $array Фикстура.
     *
     * @return void
     *
     * @dataProvider getDataFixture
     */
    public function testProcessMediasInvalidData(array $array) : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ничего не получили из Инстаграма.');

        $this->testObject->processMedias($array);
    }

    /**
     * Невалидные данные.
     *
     * @return array
     */
    public function getDataFixture() : array
    {
        return [
          [
              [
                  'test' => 'test'
              ]
          ],

            [
                [
                    'edges' => []
                ]
            ]
        ];
    }
}
