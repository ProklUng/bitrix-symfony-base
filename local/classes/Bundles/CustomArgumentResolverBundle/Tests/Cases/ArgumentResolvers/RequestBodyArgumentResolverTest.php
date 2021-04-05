<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers;

use Exception;
use InvalidArgumentException;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions\ValidateErrorException;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\RequestBodyArgumentResolver;
use Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools\SampleControllerBody;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools\SampleControllerMismatched;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Traits\ArgumentResolverTrait;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleControllerArguments;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use ReflectionException;

/**
 * Class RequestBodyArgumentResolverTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers
 * @coversDefaultClass RequestBodyArgumentResolver
 *
 * @since 03.04.2021
 */
class RequestBodyArgumentResolverTest extends BaseTestCase
{
    use ArgumentResolverTrait;

    /**
     * @var RequestBodyArgumentResolver $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @var string $controllerClass Класс контроллера.
     */
    private $controllerClass = SampleControllerBody::class;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = static::$testContainer->get('custom_arguments_resolvers.unserialize_request');
    }

    /**
     * supports(). Нормальный запрос.
     *
     * @return void
     * @throws Exception
     */
    public function testSupports(): void
    {
        $request = $this->createRequestJson(
            $this->controllerClass,
            [
                'email' => $this->faker->email,
                'numeric' => $this->faker->numberBetween(1, 100),
            ],
        );

        $result = $this->obTestObject->supports(
            $request,
            $this->getMetaArgument('unserialized', RequestBodyConverted::class)
        );

        $this->assertTrue($result, 'Неправильно определился годный к обработке контроллер');
    }

    /**
     * supports(). Короткая форма аннотации.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsShortForm(): void
    {
        $result = $this->obTestObject->supports(
            $this->createRequestJson(
                $this->controllerClass,
                [
                    'email' => $this->faker->email,
                    'numeric' => $this->faker->numberBetween(1, 100),
                ],
                'actionShort'
            ),
            $this->getMetaArgument('unserialized', RequestBodyConverted::class)
        );

        $this->assertTrue($result, 'Неправильно определился годный к обработке контроллер');
    }

    /**
     * supports(). Mismatched argument.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsMismatchedArgument(): void
    {
        $request = $this->createRequestJson(
            SampleControllerMismatched::class,
            [
                'email' => $this->faker->email,
                'numeric' => $this->faker->numberBetween(1, 100),
            ],
            'action3'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->obTestObject->supports(
            $request,
            $this->getMetaArgument('unserialized')
        );
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
            $this->createRequestJson(
                $this->controllerClass,
                [
                    'email' => $this->faker->email,
                    'numeric' => $this->faker->numberBetween(1, 100),
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
     * supports(). Не POST запрос.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsNoGetQuery(): void
    {
        $request =  $this->createRequestJson($this->controllerClass, [
            'email' => $this->faker->email,
            'numeric' => $this->faker->numberBetween(1, 100),
        ]);
        $request->setMethod('GET');

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
            $this->createRequestJson(
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

    /**
     * resolve(). Проверка вызова валидатора.
     *
     * @return void
     * @throws ValidateErrorException | ReflectionException
     */
    public function testResolveCallValidation(): void
    {
        $this->obTestObject = new RequestBodyArgumentResolver(
            static::$testContainer->get('annotations.reader'),
            static::$testContainer->get('Symfony\Component\HttpKernel\Controller\ControllerResolver'),
            static::$testContainer->get('serializer'),
            $this->getMockValidator(true)
        );

        $request = $this->createRequestJson(
            $this->controllerClass,
            [
                'email' => $this->faker->email,
                'numeric' => $this->faker->numberBetween(1, 100),
            ]
        );

        // Проверка на то, что исключение не выбрасывается.
        $result = iterator_to_array($this->obTestObject->resolve(
            $request,
            $this->getMetaArgument('unserialized', RequestBodyConverted::class)
        ));

        $this->assertTrue(true);
    }

    /**
     * resolve(). Проверка обработки опции validate.
     *
     * @return void
     * @throws ValidateErrorException | ReflectionException
     */
    public function testResolveValidationOption(): void
    {
        $this->obTestObject = new RequestBodyArgumentResolver(
            static::$testContainer->get('annotations.reader'),
            static::$testContainer->get('Symfony\Component\HttpKernel\Controller\ControllerResolver'),
            static::$testContainer->get('serializer'),
            $this->getMockValidator(false)
        );

        $request = $this->createRequestJson(
            $this->controllerClass,
            [
                'email' => $this->faker->email,
                'numeric' => $this->faker->numberBetween(1, 100),
            ],
            'actionNoValidate'
        );

        $result = iterator_to_array($this->obTestObject->resolve(
            $request,
            $this->getMetaArgument('unserialized', RequestBodyConverted::class)
        ));

        $this->assertTrue(true);
    }
}
