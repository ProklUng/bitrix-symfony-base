<?php

namespace Local\Bundles\CustomRequestResponserBundle\Tests;


use Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed\CollapseWhitespace;

/**
 * Class CollapseWhitespaceTest
 * @package Local\Bundles\CustomRequestResponserBundle\Tests
 *
 * @since 21.02.2021
 */
class CollapseWhitespaceTest extends BaseTestCase
{
    /**
     * @var CollapseWhitespace $testObject Тестируемый объект.
     */
    protected $testObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testObject = new CollapseWhitespace();
    }

    /**
     * apply(). Пробелы.
     *
     * @param string $content Контент.
     *
     * @return void
     *
     * @dataProvider dataProviderHtml
     */
    public function testApply(string $content) : void
    {
        $result = $this->testObject->apply($content);
        $this->assertSame('<div></div>', $result, 'Обработка завершилась неудачей.');
    }

    /**
     * @return \string[][]
     */
    public function dataProviderHtml() : array
    {
        return [
          'whitespaces' =>['<div>  </div>'],
          'comments' => ['<div><!-- Comments--></div>'],
          'perenos' => ["<div>\r</div>"],
          'perenos2' => ["<div>\r\n</div>"],
          'perenos3' => ["<div>\t</div>"],
        ];
    }
}