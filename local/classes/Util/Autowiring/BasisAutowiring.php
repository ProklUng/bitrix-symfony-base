<?php

namespace Local\Util\Autowiring;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;
use zpt\anno\AnnotationFactory;

/**
 * Class BasisAutowiring
 * @package Local\Util\Autowiring
 */
class BasisAutowiring
{
    /** @var  AnnotationFactory $factoryAnnotation Фабрика аннотаций. */
    private $factoryAnnotation;
    /** @var ResolveDependencyMaker $resolverDependency Ресолвер зависмостей. */
    private $resolverDependency;

    /** @var array */
    private $argsConstructor = [];
    /** @var array $arArguments Аргументы конструктора. */
    private $arArguments = [];
    /** @var array $arDepends Секция @depends конструктора. */
    private $arDepends = [];

    /**
     * Basis constructor.
     */
    public function __construct()
    {
        // Автовайринг по свойствам.
        $obPropertiesWiring = new PropertiesWiring(
            $this,
            container() // Экземпляр сервис-контейнера.
        );
        // Ресолвер зависимостей.
        $this->resolverDependency = new ResolveDependencyMaker();

        $obPropertiesWiring->wire();

        $this->factoryAnnotation = new AnnotationFactory();

        // Аргументы конструктора.
        try {
            $this->arArguments = $this->getArguments(
                $this
            );
        } catch (ReflectionException $e) {
            return;
        }

        $this->arDepends = $this->getDependsSectionAnnotations();

        // Опция @autowiring.
        try {
            if ($this->isAutoWiredClass()) {
                $this->makeAutoWiring();
            }
        } catch (ReflectionException $e) {
        }

        foreach ($this->arArguments as $param) {
            $name = $param->getName();
            // Если свойство приватное, то ничего не пытаемся делать.
            $isPrivateProperty = $this->isPrivateProperty($this, $name);

            if (!$isPrivateProperty) {
                $this->argsConstructor[$name] = $this->{$name} ?? (string)$this->{$name};
            }
        }

        // Инициализировать аргументы.
        $args = $this->initArguments($this->arArguments);
        // Установить переменные.
        $this->setVariables($args);
    }

    /**
     * Автовайринг.
     *
     * @return void
     * @throws ReflectionException
     */
    private function makeAutoWiring(): void
    {
        /**
         * Заполнить переменные класса инициализированными аргументами.
         *
         * @var string              $key   Название аргумента.
         * @var ReflectionParameter $param Параметр.
         */

        foreach ($this->arArguments as $param) {
            $name = $param->getName();
            $class = $param->getClass();

            $resolved = $param->getDefaultValue();
            if (class_exists($class->name)) {
                $resolved = $this->resolverDependency->resolveDependencies(
                    $class->name,
                    $this->arDepends
                );
            }

            // Нюанс - если параметр уже установлен, то ничего не делать, не менять его.
            $this->argsConstructor[$name] = $this->{$name} ?? $resolved;
        }

        $this->setVariables($this->argsConstructor);
    }

    /**
     * Есть ли переменная в классе?
     *
     * @param mixed  $object  Объект.
     * @param string $varName Переменная.
     *
     * @return boolean
     */
    private function hasPropertyClass($object, string $varName): bool
    {
        $arProps = get_class_vars(get_class($object));

        return array_key_exists($varName, $arProps);
    }

    /**
     * Аргументы конструктора по имени класса.
     *
     * @param mixed $object Название класса.
     *
     * @return array
     *
     * @throws ReflectionException
     */
    private function getArguments($object): array
    {
        if ($object instanceof Closure) {
            return [];
        }

        $reflector = new ReflectionClass($object);

        return $reflector->getConstructor()->getParameters();
    }

    /**
     * Инициализировать аргументы.
     *
     * @param array $arArguments Аргументы конструктора.
     *
     * @return array
     */
    private function initArguments(array $arArguments): array
    {
        $arResultArguments = [];
        /** @var ReflectionParameter $argument */

        foreach ($arArguments as $argument) {
            $nameVar = $argument->getName();

            // Аннотации конструктора.
            try {
                $annotationsMethod = $this->getAnnotationsConstructor($nameVar);
            } catch (ReflectionException $e) {
                $annotationsMethod = [];
            }

            // Если свойство инциализировано (и оно неприватное), то инжекцию пропускаем.
            $nameProperty = $argument->name;
            if (!$this->isPrivateProperty($this, $nameProperty)
                &&
                $this->{$nameProperty} !== null
                &&
                $this->hasPropertyClass($this, $nameProperty)
            ) {
                continue;
            }

            // Класс
            $class = $argument->getClass();

            if ($class !== null
                &&
                (class_exists($class->name) || interface_exists($class->name))
            ) {
                // Аннотация injectDependency в конструкторе.
                $sectionInjectDependency = $this->getDependsAnnotations($nameVar);

                // Собрать все возможные зависимости в один массив.
                $fullDependency = array_merge($annotationsMethod, $this->arDepends, $sectionInjectDependency);
                $fullDependency = array_unique($fullDependency);

                $arResultArguments[$nameVar] = $this->resolverDependency->resolveDependencies(
                    $class->name,
                    $fullDependency
                );

                continue;
            }

            // Инициализировать аргументы из аннотации.
            $preparedArguments = $this->prepareArguments(
                $annotationsMethod
            );

            // Функция в docblock.
            if (!empty($annotationsMethod[0])
                && is_callable($annotationsMethod[0])
            ) {
                $arResultArguments[$nameVar] = $annotationsMethod[0](...$preparedArguments);
                continue;
            }

            // Просто переменная. Значение по умолчанию.
            try {
                $arResultArguments[$nameVar] = $argument->getDefaultValue();
                // Callable аннотация.
                if (!empty($preparedArguments)) {
                    $arResultArguments[$nameVar] = current($preparedArguments);
                }
            } catch (ReflectionException $e) {
                $arResultArguments[$nameVar] = null;
            }
        }

        return $arResultArguments;
    }

    /**
     * Этот класс предполагает автовайринг аргументов?
     *
     * @return boolean
     * @throws ReflectionException
     */
    private function isAutoWiredClass(): bool
    {
        $annotations = $this->getMethodAnnotation(
            get_class($this),
            '__construct',
            'autowiring'
        );

        return $annotations === true;
    }

    /**
     * Все метки @depends в аннотациях конструктора.
     *
     * @return array
     */
    private function getDependsSectionAnnotations(): array
    {
        try {
            $annotations = $this->getMethodAnnotation(
                get_class($this),
                '__construct',
                'depends'
            );
        } catch (ReflectionException $e) {
            return [];
        }

        return collect($annotations)->flatten()->toArray();
    }

    /**
     * Секция @injectDependency в аннотациях конструктора.
     *
     * @param string $varName
     *
     * @return array
     */
    private function getDependsAnnotations(string $varName): array
    {
        $varName = str_replace('$', '', $varName);

        try {
            $annotations = $this->getMethodAnnotation(
                get_class($this),
                '__construct',
                'injectDependency'
            );
        } catch (ReflectionException $e) {
            return [];
        }

        return !empty($annotations[$varName]) ? $annotations[$varName] : [];
    }

    /**
     * Установить переменные класса.
     *
     * @param array $arArguments Аргументы.
     *
     * @internal Инициализируются в свойства класса. Если нет, игнорируется.
     */
    private function setVariables(array $arArguments): void
    {
        foreach ($arArguments as $name => $argItem) {
            if ($this->hasPropertyClass($this, $name)) {
                $this->{$name} = $argItem;
            }
        }
    }

    /**
     * Подготовить аргументы.
     *
     * @param mixed $arArguments
     *
     * @return array
     */
    private function prepareArguments($arArguments): array
    {
        /** Результирующий массив с инициализированными аргументами. */
        $arguments = [];
        if (!is_array($arArguments) || count($arArguments) === 0) {
            return [];
        }

        foreach ($arArguments as $argumentItem) {
            if (is_callable($argumentItem)) {
                // Отресолвим зависимости статической функции(метода).
                $arguments = $this->resolverDependency->resolveDependenciesCallable(
                    $argumentItem
                );

                $arguments[] = $argumentItem(...$arguments);
                continue;
            }

            $argument = $argumentItem;
            if (class_exists($argumentItem)) {
                $argument = $this->resolverDependency->resolveDependencies(
                    $argumentItem
                );
            }

            $arguments[] = $argument;
        }

        return $arguments;
    }

    /**
     * Аннотация метода.
     *
     * @param string $sClassName Название класса.
     * @param string $method     Метод.
     * @param string $varName    Переменная.
     *
     * @return array|mixed
     * @throws ReflectionException
     */
    private function getMethodAnnotation(string $sClassName, string $method, string $varName)
    {
        $classReflector = new ReflectionClass($sClassName);

        /** Аннотации метода. */
        $methodAnnotations = [];
        /** Результирующий массив. */
        $params = [];

        foreach ($classReflector->getMethods() as $methodReflector) {
            $currentMethod = $methodReflector->getName();
            if ($currentMethod === $method) {
                $methodAnnotations[$currentMethod] = $this->factoryAnnotation->get($methodReflector);

                if (!empty($methodAnnotations[$currentMethod][$varName])) {
                    $params = $methodAnnotations[$currentMethod][$varName];
                }
            }
        }

        return $params;
    }

    /**
     * Аннотации конструктора.
     *
     * @param string $nameVar Переменная в конструкторе.
     *
     * @return array|mixed
     * @throws ReflectionException
     */
    private function getAnnotationsConstructor(string $nameVar)
    {
        return $this->getMethodAnnotation(
            get_class($this),
            '__construct',
            $nameVar
        );
    }

    /**
     * Свойство приватное?
     *
     * @param mixed  $object   Объект.
     * @param string $property Свойство.
     *
     * @return bool|null
     */
    private function isPrivateProperty($object, string $property) : ?bool
    {
        try {
            $reflection = new ReflectionProperty(get_class($object), $property);
        } catch (ReflectionException $e) {
            return null;
        }

        return $reflection->isPrivate();
    }
}
