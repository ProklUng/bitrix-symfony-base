<?php

namespace Local\Bundles\CustomRequestResponserBundle\Tests;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseTestCase
 * @package Local\Bundles\CustomRequestResponserBundle\Tests
 */
class BaseTestCase extends TestCase
{

    /**
     * @var mixed $testObject Тестируемый объект.
     */
    protected $testObject;

    /**
     * @var Generator | null $faker
     */
    protected $faker;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
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

        $this->testObject = null;
    }

    /**
     * @param string $url
     *
     * @return Request
     */
    protected function getRequest(string $url) : Request
    {
        $fakeRequest = Request::create(
            $url,
            'GET',
            []
        );

        return $fakeRequest;
    }
}