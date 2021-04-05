<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Service\ResolversDependency;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * Class ResolveDependencyMaker
 * Реализация рекурсивного разрешения зависимостей.
 * @package Local\Bundles\CustomArgumentResolverBundle\Service\ResolversDependency
 */
class ResolveDependencyMaker
{
    /**
     * @var array $arDepends Массив сопоставлений интерфейс - реализация.
     */
    protected $arDepends = [];

    /**
     * Сеттер сопоставлений.
     *
     * @param array $arDepends
     *
     * @return ResolveDependencyMaker
     */
    public function setDepends(array $arDepends): ResolveDependencyMaker
    {
        $this->arDepends = $arDepends;

        return $this;
    }

    /**
     * Разрешить зависимости callable.
     *
     * @param string|callable $callable
     *
     * @return array|null
     * @throws RuntimeException
     */
    public function resolveDependenciesCallable(string $callable): ?array
    {
        $arResult = [];

        if (!is_callable($callable)) {
            return null;
        }

        // Вычленить метод и класс.
        $arParse = explode('::', $callable);

        if (!class_exists($arParse[0])) {
            throw new RuntimeException(
                sprintf(
                    'Class %s not exist. Try use as callable.',
                    $arParse[0]
                )
            );
        }

        try {
            $reflectionCallable = new ReflectionMethod($arParse[0], $arParse[1]);
        } catch (ReflectionException $e) {
            return null;
        }

        $param = $reflectionCallable->getParameters();

        foreach ($param as $item) {
            $class = $item->getClass();

            // В параметрах класс.
            if ($class !== null && class_exists($class->name)) {
                $arResult[] = $this->resolveDependencies($class->name);
                continue;
            }

            // Не класс - integer, string & etc.
            try {
                $defaultValue = $item->getDefaultValue();
            } catch (ReflectionException $e) {
                $defaultValue = null;
            }

            $arResult[] = $defaultValue;
        }

        return $arResult;
    }

    /**
     * Разрешить зависимости класса автоматически.
     *
     * @param string $class     Название класса.
     * @psalm-param class-string $class
     * @param array  $arDepends Реализации интерфейсов.
     *
     * @return object|null
     */
    public function resolveDependencies(string $class, array $arDepends = [])
    {
        // Реализации - приоритет имеет переданное напрямую.
        $arDepends = $arDepends ?: $this->arDepends;

        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            return null;
        }

        // Интерфейсы.
        if ($reflectionClass->isInterface()) {
            // Реализация интерфейса.
            $realizationInterface = $this->tryResolveInterface($class, $arDepends);
            if (!$realizationInterface) {
                return null;
            }

            return $this->resolveDependencies($realizationInterface, $arDepends);
        }

        // Абстрактные классы.
        if ($reflectionClass->isAbstract()) {
            $realizationAbstractClass = $this->tryResolveAbstractClass(
                $class, $arDepends
            );

            if (!$realizationAbstractClass) {
                return null;
            }

            return $this->resolveDependencies($realizationAbstractClass, $arDepends);
        }

        // Fetch the constructor (instance of ReflectionMethod)
        $constructor = $reflectionClass->getConstructor();

        // If there is no constructor, there is no
        // dependencies, which means that our job is done.
        if (!$constructor) {
            return new $class;
        }

        // Fetch the arguments from the constructor
        // (collection of ReflectionParameter instances)
        $params = $constructor->getParameters();

        // If there is a constructor, but no dependencies,
        // our job is done.
        if (count($params) === 0) {
            return new $class;
        }

        // This is were we store the dependencies
        $newInstanceParams = [];

        // Loop over the constructor arguments
        foreach ($params as $param) {
            // Here we should perform a bunch of checks, such as:
            // isArray(), isCallable(), isDefaultValueAvailable()
            // isOptional() etc.

            // For now, we just check to see if the argument is
            // a class, so we can instantiate it,
            // otherwise we just pass null.
            if (is_null($param->getClass())) {
                try {
                    $defaultValueArgument = $param->getDefaultValue();
                } catch (ReflectionException $e) {
                    $defaultValueArgument = null;
                }

                $newInstanceParams[] = $defaultValueArgument;

                continue;
            }

            $paramClass = $param->getClass();
            if ($paramClass) {
                $paramClass = $paramClass->getName();
            }

            $paramClass = (string)$paramClass;

            if (!class_exists($paramClass)) {
                throw new RuntimeException(
                    sprintf(
                        'Class %s not exist. Try resolve dependencies.',
                        $paramClass
                    )
                );
            }

            // This is where 'the magic happens'. We resolve each
            // of the dependencies, by recursively calling the
            // resolve() method.
            $newInstanceParams[] = $this->resolveDependencies(
                $paramClass,
                $arDepends
            );
        }

        // Return the reflected class, instantiated with all its
        // dependencies (this happens once for all the
        // nested dependencies).
        return $reflectionClass->newInstanceArgs(
            $newInstanceParams
        );
    }

    /**
     * Сопоставление интерфейса и реализации.
     *
     * @param string $interfaceClass Интерфейс.
     * @param array  $arDepends      Зависимости.
     *
     * @return bool|mixed|string
     */
    protected function tryResolveInterface(
        string $interfaceClass,
        array $arDepends = []
    ) {
        if (!$arDepends) {
            return false;
        }

        foreach ($arDepends as $realization) {
            $interfaces = class_implements($realization);
            if ($interfaces && in_array($interfaceClass, $interfaces, true)) {
                return $realization;
            }
        }

        return '';
    }

    /**
     * Сопоставление абстрактного класса и реализации.
     *
     * @param string $abstractClass Абстрактный класс.
     * @param array  $arDepends     Зависимости.
     *
     * @return mixed|string
     */
    protected function tryResolveAbstractClass(
        string $abstractClass,
        array $arDepends = []
    ) {
        if (!$arDepends) {
            return '';
        }

        foreach ($arDepends as $realization) {
            if (is_subclass_of($realization, $abstractClass)) {
                return $realization;
            }
        }

        return '';
    }
}
