<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\BitrixFileArgumentResolver;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\RequestBodyArgumentResolver;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools\SampleControllerBitrixFile;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Traits\ArgumentResolverTrait;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleControllerArguments;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;

/**
 * Class BitrixFileArgumentResolverTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers
 * @coversDefaultClass BitrixFileArgumentResolver
 *
 * @since 03.04.2021
 */
class BitrixFileArgumentResolverTest extends BaseTestCase
{
    use ArgumentResolverTrait;

    /**
     * @var BitrixFileArgumentResolver $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @var string $controllerClass Класс контроллера.
     */
    private $controllerClass = SampleControllerBitrixFile::class;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = static::$testContainer->get('custom_arguments_resolvers.bitrix_file');
    }

    /**
     * supports(). Нормальный запрос.
     *
     * @return void
     * @throws Exception
     */
    public function testSupports(): void
    {
        $request = $this->createRequest(
            $this->controllerClass,
            [
                'file' => 27,
            ],
        );

        $result = $this->obTestObject->supports(
            $request,
            $this->getMetaArgument('file')
        );

        $this->assertTrue($result, 'Неправильно определился годный к обработке контроллер');
    }

    /**
     * supports(). Нет нужного параметра в Request.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsNoParam(): void
    {
        $result = $this->obTestObject->supports(
            $this->createRequest(
                $this->controllerClass,
                [
                    'file' => 27,
                ],
            ),
            $this->getMetaArgument('unknown')

        );

        $this->assertFalse(
            $result,
            'Неправильно определился контроллер с отсутствующим параметром'
        );
    }

    /**
     * supports(). POST запрос.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsPostQuery(): void
    {
        $request = $this->createRequestPost(
            $this->controllerClass,
            [
                'file' => 27,
            ],
        );

        $result = $this->obTestObject->supports(
            $request,
            $this->getMetaArgument('unserialized')
        );

        $this->assertFalse(
            $result,
            'Неправильно определился негодный к обработке тип запроса'
        );
    }

    /**
     * supports(). Контроллер без аннотации.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsNoAnnotations(): void
    {
        $result = $this->obTestObject->supports(
            $this->createRequest(
                SampleControllerArguments::class,
                [
                    'email' => $this->faker->email,
                    'numeric' => $this->faker->numberBetween(1, 100),
                ],
            ),
            $this->getMetaArgument('unserialized')
        );

        $this->assertFalse(
            $result,
            'Неправильно определился негодный к обработке тип запроса'
        );
    }
}
