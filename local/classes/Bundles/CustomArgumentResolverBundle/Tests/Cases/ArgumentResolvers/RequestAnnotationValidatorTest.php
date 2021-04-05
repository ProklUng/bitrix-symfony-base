<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions\ValidateErrorException;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Validator\RequestAnnotationValidator;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools\ExampleRequestClass;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class RequestAnnotationValidatorTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers
 * @coversDefaultClass RequestAnnotationValidator
 *
 * @since 03.04.2021
 */
class RequestAnnotationValidatorTest extends BaseTestCase
{
    /**
     * @var RequestAnnotationValidator $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new RequestAnnotationValidator(
            static::$testContainer->get('annotations.reader'),
            Validation::createValidator(),
            static::$testContainer->get('serializer'),
        );
    }

    /**
     * validate(). Нормальный ход вещей.
     *
     * @return void
     * @throws Exception
     */
    public function testValidate() : void
    {
        $object = new class {
            public $email = 'xxxxxxxxxxxxxxxx';
        };

        $this->obTestObject->validate(
            $object,
            ExampleRequestClass::class
        );

        $this->assertTrue(true);
    }

    /**
     * validate(). Невалидный параметр.
     *
     * @return void
     * @throws Exception
     */
    public function testValidateInvalidValue() : void
    {
        $object = new class {
            public $email = 'x';
        };

        $this->expectException(ValidateErrorException::class);
        $this->obTestObject->validate(
            $object,
            ExampleRequestClass::class
        );
    }

    /**
     * validate(). В параметрах объекта нет валидируемого значения.
     *
     * @return void
     * @throws Exception
     */
    public function testValidateWithoutValue() : void
    {
        $object = new class {
            public $dummy;
        };

        $this->obTestObject->validate(
            $object,
            ExampleRequestClass::class
        );

        $this->assertTrue(true);
    }
}
