<?php

namespace Local\Bundles\CustomRequestResponserBundle\Tests;

use Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed\InsertDNSPrefetch;
use Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed\TrimUrl;

/**
 * Class TrimUrlTest
 * @package Local\Bundles\CustomRequestResponserBundle\Tests
 *
 * @since 21.02.2021
 */
class TrimUrlTest extends BaseTestCase
{
    /**
     * @var TrimUrl $testObject Тестируемый объект.
     */
    protected $testObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testObject = new TrimUrl();
    }

    /**
     * apply().
     *
     * @param string $content
     *
     * @return void
     *
     * @dataProvider dataProviderHtml
     */
    public function testApply(string $content) : void
    {
        $result = $this->testObject->apply($content);
        $this->assertStringContainsString(
            'href="//fake.domain/ie/"',
            $result,
            'В результате появились какие-то левые данные.'
        );
    }

    /**
     * apply().
     *
     * @param string $content
     *
     * @return void
     *
     * @dataProvider dataProviderMiscHtml
     */
    public function testApplyInvalid(string $content) : void
    {
        $result = $this->testObject->apply($content);

        $this->assertSame(
            $content,
            $result,
            'В результате появились какие-то левые данные.'
        );
    }

    /**
     * @return \string[][]
     */
    public function dataProviderHtml() : array
    {
        return [
            'set 1' =>['<div><a href="https://fake.domain/ie/"></a></div>'],
            'set 2' =>['<div><a href="http://fake.domain/ie/"></a></div>'],
        ];
    }


    /**
     * @return \string[][]
     */
    public function dataProviderMiscHtml() : array
    {
        return [
            'set 1' =>['<div><a href="/ie/"></a></div>'],
            'set 2' =>['<div>Test</div>'],
        ];
    }
}