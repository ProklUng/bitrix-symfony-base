<?php

namespace Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Traits;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait ArgumentResolverTrait
 * @package Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Traits
 *
 * @since 02.04.2021
 */
trait ArgumentResolverTrait
{
    /**
     * @param Request $request Request.
     * @param string  $class   Класс.
     *
     * @return mixed
     * @throws ReflectionException Ошибки рефлексии.
     */
    private function getAnnotation(Request $request, string $class = self::DEFAULT_ANNOTATION)
    {
        $controller = $this->controllerResolver->getController($request);
        if (!is_array($controller)) {
            return false;
        }

        $method = new ReflectionMethod($controller[0], $controller[1]);

        return $this->reader->getMethodAnnotation($method, $class);
    }

    /**
     * Класс наследует от SpatieDTO?
     *
     * @param string $className Название класса.
     *
     * @return boolean
     * @throws ReflectionException Ошибки рефлексии.
     *
     * @since 01.04.2021
     */
    private function isSpatieDto(string $className) : bool
    {
        $parentClasses = $this->getClassNames($className);

        if ($parentClasses) {
            foreach ($parentClasses as $parentClass) {
                if ($parentClass === DataTransferObject::class) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Родительские классы.
     *
     * @param string $className Класс, подвергающийся обработке.
     *
     * @return array
     * @throws ReflectionException Ошибки рефлексии.
     *
     * @since 01.04.2021
     */
    private function getClassNames(string $className) : array
    {
        if (!class_exists($className)) {
            throw new ReflectionException(
                'Class ' . $className . ' not exist.'
            );
        }

        $ref = new ReflectionClass($className);
        $parentRef = $ref->getParentClass();

        return array_unique(array_merge(
            [$className],
            $ref->getInterfaceNames(),
            $ref->getTraitNames(),
            $parentRef ?$this->getClassNames($parentRef->getName()) : []
        ));
    }

    /**
     * Подходящий тип запроса?
     *
     * @param Request $request Request.
     *
     * @return boolean
     */
    private function isValidRequestType(Request $request) : bool
    {
        $method = $request->getMethod();

        if ($method === 'GET' || $method === 'HEAD') {
            return false;
        }

        return true;
    }

    /**
     * Привести данные запроса к типам, указанным в DTO.
     *
     * @param array  $values      Значения GET параметров.
     * @param string $targetClass DTO класс.
     *
     * @return array
     * @throws ReflectionException Ошибки рефлексии.
     */
    private function castRequest(array $values, string $targetClass): array
    {
        $reflectionClass = new ReflectionClass($targetClass);
        $props = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        $result = $values;

        foreach ($props as $key => $property) {
            $nameProperty = $property->getName();
            if (!array_key_exists($nameProperty, $values)) {
                continue;
            }

            $docbloc = $this->extractor->getTypes($targetClass, $property->getName());
            if (array_key_exists(0, $docbloc)) {
                $type = $docbloc[0]->getBuiltinType();
                if ($type === 'int') {
                    $result[$nameProperty] = (int)$result[$nameProperty];
                }

                if ($type === 'string') {
                    $result[$nameProperty] = (string)$result[$nameProperty];
                }

                if ($type === 'bool') {
                    $result[$nameProperty] = (bool)$result[$nameProperty];
                }

                if ($type === 'float') {
                    $result[$nameProperty] = (float)$result[$nameProperty];
                }
            }
        }

        return $result;
    }

    /**
     * Это JSON?
     *
     * @param mixed $value Тестируемое значение.
     *
     * @return boolean
     */
    private function isJson($value) : bool
    {
        if ($value === '') {
            return false;
        }

        json_decode($value);

        if (json_last_error()) {
            return false;
        }

        return true;
    }

    /**
     * Исключение при несоответствии типа.
     *
     * @param string $nameVariable Название переменной.
     * @param string $typeVariable Тип переменной.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function throwMismatchException(string $nameVariable, string $typeVariable) : void
    {
        if (!class_exists($typeVariable)) {
            throw new InvalidArgumentException(
                'Mismatch type error. Variable ' .$nameVariable .
                ' marked in annotation, but ' .
                ' such  - ' . $typeVariable . ' -  class not exist.'
            );
        }
    }

    /**
     * Исключение при несоответствии имплементации интерфейса.
     *
     * @param string $nameVariable Название переменной.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function throwMismatchImplementedInterface(string $nameVariable) : void
    {
        throw new InvalidArgumentException(
            'Mismatch type error. Variable ' . $nameVariable .
            ' marked in annotation, but not implemented UnserializableRequestInterface interface'
        );
    }
}
