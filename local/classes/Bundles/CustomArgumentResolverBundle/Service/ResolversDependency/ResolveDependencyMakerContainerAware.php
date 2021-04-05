<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Service\ResolversDependency;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class ResolveDependencyMakerContainerAware
 * Реализация рекурсивного разрешения зависимостей с учетом сервис-контейнера.
 * @package Local\Bundles\CustomArgumentResolverBundle\Service\ResolversDependency
 *
 * @since 12.10.2020
 */
class ResolveDependencyMakerContainerAware extends ResolveDependencyMaker
{
    use ContainerAwareTrait;

    /**
     * Разрешить зависимости класса автоматически.
     *
     * @param string $class     Название класса.
     * @param array  $arDepends Реализации интерфейсов.
     *
     * @return object|null
     * @throws ReflectionException
     */
    public function resolveDependencies(string $class, array $arDepends = [])
    {
        // Из контейнера сначала.
        if ($this->container->has($class)) {
            return $this->container->get($class);
        }

        // Реализации - приоритет имеет переданное напрямую.
        $arDepends = $arDepends ?: $this->arDepends;

        if (!class_exists($class)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($class);

        // Интерфейсы.
        if ($reflectionClass->isInterface()) {
            // Реализация интерфейса.
            $realizationInterface = $this->tryResolveInterface($class, $arDepends);
            if (!$realizationInterface) {
                return null;
            }

            if (is_object($realizationInterface)) {
                return $realizationInterface;
            }

            return $this->resolveDependencies($realizationInterface, $arDepends);
        }

        // Абстрактные классы.
        if ($reflectionClass->isAbstract()) {
            $realizationAbstractClass = $this->tryResolveAbstractClass(
                $class,
                $arDepends
            );

            if (is_object($realizationAbstractClass)) {
                return $realizationAbstractClass;
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

            /**
             * Название класса из рефлексии.
             * @psalm-suppress PossiblyNullReference
             */
            $className = $param->getClass()->getName();

            // Пытаюсь достать из контейнера.
            if ($this->container->has($className)) {
                $newInstanceParams[] = $this->container->get($className);
                continue;
            }

            // This is where 'the magic happens'. We resolve each
            // of the dependencies, by recursively calling the
            // resolve() method.
            $newInstanceParams[] = $this->resolveDependencies(
                $className,
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
     * @return boolean|mixed|string
     */
    protected function tryResolveInterface(
        string $interfaceClass,
        array $arDepends = []
    ) {
        if ($this->container->has($interfaceClass)) {
            return $this->container->get($interfaceClass);
        }

        return parent::tryResolveInterface($interfaceClass, $arDepends);
    }

    /**
     * Сопоставление абстрактного класса и реализации.
     *
     * @param string $abstractClass Абстрактный класс.
     * @param array  $arDepends     Зависимости.
     *
     * @return boolean|mixed|string
     */
    protected function tryResolveAbstractClass(
        string $abstractClass,
        array $arDepends = []
    ) {
        if ($this->container->has($abstractClass)) {
            return $this->container->get($abstractClass);
        }

        return parent::tryResolveAbstractClass($abstractClass, $arDepends);
    }
}
