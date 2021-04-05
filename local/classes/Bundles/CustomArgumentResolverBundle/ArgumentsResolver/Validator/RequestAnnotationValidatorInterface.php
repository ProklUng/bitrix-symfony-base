<?php

namespace Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Validator;

use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions\ValidateErrorException;
use ReflectionException;

/**
 * Interface RequestAnnotationValidatorInterface
 * @package Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Validator
 *
 * @since 01.04.2021
 */
interface RequestAnnotationValidatorInterface
{
    /**
     * @param object $object Объект, подлежащий валидации.
     * @param string $class  Класс.
     *
     * @return void
     * @throws ReflectionException    Ошибки рефлексии.
     * @throws ValidateErrorException Ошибки валидации.
     */
    public function validate($object, string $class) : void;
}
