<?php

namespace Local\Bundles\CustomRequestResponserBundle\Tests;

use Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed\InsertDNSPrefetch;

/**
 * Class InsertDNSPrefetchTest
 * @package Local\Bundles\CustomRequestResponserBundle\Tests
 *
 * @since 21.02.2021
 */
class InsertDNSPrefetchTest extends BaseTestCase
{
    /**
     * @var InsertDNSPrefetch $testObject Тестируемый объект.
     */
    protected $testObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testObject = new InsertDNSPrefetch();
    }

    /**
     * apply().
     *
     * @return void
     */
    public function testApply() : void
    {
        $content = '<html><head><link rel="stylesheet" href="http://fake.domain/ie.css"></head></html>';
        $result = $this->testObject->apply($content);

        $this->assertStringContainsString(
            'link rel="dns-prefetch" href="//fake.domain"',
            $result,
            'Обработка завершилась неудачей.'
        );
    }

    /**
     * apply().
     *
     * @return void
     */
    public function testApplyWithoutDomain() : void
    {
        $content = '<html><head><link rel="stylesheet" href="/ie.css"></head></html>';
        $result = $this->testObject->apply($content);

        $this->assertStringNotContainsString(
            'link rel="dns-prefetch" href="//fake.domain"',
            $result,
            'В результате появились какие-то левые данные.'
        );
    }
}