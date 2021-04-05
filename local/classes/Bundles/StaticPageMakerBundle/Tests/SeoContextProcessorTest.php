<?php

namespace Local\Bundles\StaticPageMakerBundle\Tests;

use Faker\Factory;
use Faker\Generator;
use Local\Bundles\StaticPageMakerBundle\Services\Bitrix\SeoMetaElement;
use Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors\SeoContextProcessor;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class SeoContextProcessortTest
 * @package Local\Bundles\StaticPageMakerBundle\Tests
 *
 * @since 24.01.2021
 */
class SeoContextProcessorTest extends TestCase
{
    /**
     * @var SeoContextProcessor $testObject
     */
    private $testObject;

    /**
     * @var Generator | null $faker
     */
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     * handle(). No url in context.
     *
     * @return void
     */
    public function testHandleNoUrlInContext() : void
    {
        $this->testObject = new SeoContextProcessor(
            $this->getMockSeoMetaElement('', '')
        );

        $context = ['test' => 'test'];

        $this->testObject->setContext($context);

        $this->assertSame(
            $context,
            $this->testObject->handle(),
            'Контекст изменился, а не должен.'
        );
    }

    /**
     * handle(). No url in context.
     *
     * @return void
     */
    public function testHandleInvalidUrlInContext() : void
    {
        $this->testObject = new SeoContextProcessor(
            $this->getMockSeoMetaElementThrowException()
        );

        $context = ['test' => 'test', 'url' => '/url/'];

        $this->testObject->setContext($context);

        $this->assertSame(
            $context,
            $this->testObject->handle(),
            'Контекст изменился, а не должен.'
        );
    }

    /**
     * handle(). Обработка titles & description.
     *
     * @return void
     */
    public function testHandleTitleDescription() : void
    {
        $title = $this->faker->title;
        $description = $this->faker->sentence();

        $this->testObject = new SeoContextProcessor(
            $this->getMockSeoMetaElement($title, $description)
        );

        $context = ['test' => 'test', 'url' => $this->faker->url];

        $this->testObject->setContext($context);

        $result = $this->testObject->handle();

        $this->assertSame(
            $title,
            $result['title'],
            'Title не такой, какой должен быть.'
        );

        $this->assertSame(
            $description,
            $result['description'],
            'Description не такой, какой должен быть.'
        );
    }

    /**
     * Мок SeoMetaElement.
     *
     * @param string $title
     * @param string $description
     *
     * @return mixed
     */
    private function getMockSeoMetaElement(string $title, string $description) {
        $mock = Mockery::mock(SeoMetaElement::class)
                ->makePartial();

        $mock->shouldReceive('data')->andReturn($mock);
        $mock->shouldReceive('title')->andReturn($title);
        $mock = $mock->shouldReceive('description')->andReturn($description)
             ->getMock();

        return $mock;
    }

    /**
     * Мок SeoMetaElement. Выбрасывает исключение по инвалидному URL.
     *
     * @return mixed
     */
    private function getMockSeoMetaElementThrowException() {
        $mock = Mockery::mock(SeoMetaElement::class)
            ->makePartial();

        $mock = $mock->shouldReceive('data')->andThrow(RuntimeException::class);

        return $mock->getMock();
    }
}