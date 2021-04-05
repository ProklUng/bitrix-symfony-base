<?php

namespace Local\Bundles\StaticPageMakerBundle\Tests;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\StaticPageMakerBundle\Services\TemplateController;
use LogicException;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * Class TemplateControllerTest
 * @package Local\Bundles\StaticPageMakerBundle\Tests
 *
 * @since 25.01.2021
 */
class TemplateControllerTest extends TestCase
{
    /**
     * @var TemplateController $testObject
     */
    private $testObject;

    /**
     * @var Generator | null $faker
     */
    private $faker;

    /**
     * @throws Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $loader = new FilesystemLoader(
            [$_SERVER['DOCUMENT_ROOT'] . '/local/classes/Bundles/StaticPageMakerBundle/Tests/templates']
        );

        $twig = new Environment($loader);

        $this->testObject = new TemplateController(
            $twig
        );
    }

    /**
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testNoTwigPassed() : void
    {
        $this->testObject = new TemplateController();

        $this->expectException(LogicException::class);
        $this->testObject->templateAction(
          $this->faker->sentence,
        );
    }

    /**
     * Выставляет ли заголовки.
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testSetHeaders() : void
    {
        $maxAge = $this->faker->numberBetween(200, 400);
        $sharedAge = $this->faker->numberBetween(200, 400);
        $private = true;

        $result = $this->testObject->templateAction(
            './void.twig',
            $maxAge,
            $sharedAge,
            $private
        );

        $this->assertSame(
            (string)$maxAge,
            $result->headers->getCacheControlDirective('max-age')
        );

        $this->assertSame(
            (string)$sharedAge,
            $result->headers->getCacheControlDirective('s-maxage')
        );

        $this->assertTrue(
            $result->headers->getCacheControlDirective('private')
        );
    }
}
