<?php

namespace Local\Util\Autowiring;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;
use zpt\anno\Annotations;
use zpt\anno\ReflectorNotCommentedException;

/**
 * Class PropertiesWiring
 * @package Local\Util\Autowiring
 */
class PropertiesWiring
{
    /** @var mixed $object Объект, подлежащий автовайрингу свойств. */
    private $object;

    /** @var  PropertyInfoExtractor $propertyInfo */
    private $propertyInfo;

    /** @var ResolveDependencyMaker $resolveDependencyMaker Разрешитель зависимостей. */
    private $resolveDependencyMaker;

    /**
     * @var ContainerInterface $serviceContainer Сервис контейнер.
     */
    private $serviceContainer;

    /**
     * PropertiesWiring constructor.
     *
     * @param mixed                   $object    Объект, подлежащий обработке.
     * @param ContainerInterface|null $container Сервис-контейнер.
     */
    public function __construct(
        $object,
        ContainerInterface $container = null
    ) {
        if (!is_object($object)) {
            return;
        }

        $this->object = $object;
        $this->serviceContainer = $container;

        $this->resolveDependencyMaker = new ResolveDependencyMaker();
        $this->propertyInfo = $this->initSymfonyReflector();
    }

    /**
     * Автовайринг.
     *
     * @return boolean
     */
    public function wire(): bool
    {
        if ($this->object === null) {
            return false;
        }

        // Сначала пробежаться по свойствам в поисках @required.
        $arWiredProperty = $this->getAutoWiredProperties($this->object);
        if (empty($arWiredProperty)) {
            return false;
        }

        // Инициализировать переменные. Только, если они пусты (null)!
        foreach ($arWiredProperty as $nameProperty => $initedProperty) {
            try {
                if ($this->isNullValueProperty($this->object, $nameProperty)) {
                    $this->setPropertyValue(
                        $this->object,
                        $nameProperty,
                        $initedProperty
                    );
                }
            } catch (ReflectionException $e) {
                continue;
            }
        }

        return true;
    }

    /**
     * Получить autowired property объекта.
     *
     * @param mixed $object Объект.
     *
     * @return array Возвращает инициализированные параметры
     */
    private function getAutoWiredProperties($object): array
    {
        /** @var string $className Название обрабатываемого класса. */
        $className = get_class($object);

        /** Все свойства класса. */
        $allProperties = $this->getAllPropertiesClass($object);

        /** Инциализированные переменные. Ключ - название свойства. */
        $arWiredProperty = [];

        foreach ($allProperties as $nameProperty => $propItem) {
            // Читаем аннотацию.
            $annotationProperty = $this->getAnnotationProperty($className, $nameProperty);

            // Нет @required в аннотации - игнорируем.
            if (empty($annotationProperty['required'])) {
                continue;
            }

            /**
             * Если присутствует аннотация @service, то пытаемся взять из контейнеров.
             */
            if (!empty($annotationProperty['service'])) {
                $resolvedService = $this->serviceContainer->get($annotationProperty['service']);
                if ($resolvedService !== null) {
                    $arWiredProperty[$nameProperty] = $resolvedService;
                }

                continue;
            }

            // Тип свойства из @var
            $typeProperty = $this->getTypePropertyDependency($className, $nameProperty);

            // Closure не поддерживаем.
            if ($typeProperty === 'Closure') {
                continue;
            }

            // Биндинг глобальных переменных.
            if (!empty($annotationProperty['bind'])) {
                $globalVariable = str_replace('$', '', $annotationProperty['bind']);
                $arWiredProperty[$nameProperty] = !empty($GLOBALS[$globalVariable]) ? $GLOBALS[$globalVariable] : null;

                continue;
            }

            /**
             * Обработка @args (callable etc)
             */
            if (!empty($annotationProperty['args'])
                &&
                is_callable($annotationProperty['args'])
            ) {
                // Разрешенные аргументы callable
                $resolveArgCallable = $this->resolveDependencyMaker->resolveDependenciesCallable(
                    $annotationProperty['args']
                );

                $arWiredProperty[$nameProperty] = $annotationProperty['args'](...$resolveArgCallable);
                continue;
            }

            // Рекурсивное разрешение зависимостей.
            if (class_exists($typeProperty)
                || interface_exists($typeProperty)
            ) {
                $arWiredProperty[$nameProperty] = $this->resolveDependencyMaker->resolveDependencies(
                    $typeProperty,
                    (array)$annotationProperty['depends']
                );
            }
        }

        return $arWiredProperty;
    }

    /**
     * Распарсить тип свойства объекта.
     *
     * @param string $class    Название класса.
     * @param string $property Свойство.
     *
     * @return string Название класса, если объект. Иначе пустая строка.
     *
     */
    private function getTypePropertyDependency(string $class, string $property): string
    {
        $arTypes = $this->propertyInfo->getTypes($class, $property);
        if ($arTypes === null) { // Возникает при @var mixed.
            return '';
        }

        /** @var Type $types */
        $types = current($arTypes);

        $type = $types->getBuiltinType();

        // Если объект, то вернем его класс, предварительно проверив на существование.
        if ($type === Type::BUILTIN_TYPE_OBJECT) {
            return $types->getClassName();
        }

        return '';
    }

    /**
     * Аннотации свойства.
     *
     * @param string $class    Название класса.
     * @param string $property Свойство.
     *
     * @return array|mixed
     */
    private function getAnnotationProperty(string $class, string $property)
    {
        try {
            $reflection = new ReflectionProperty(
                $class,
                $property
            );
        } catch (ReflectionException $e) {
            return [];
        }

        // Private свойства не обрабатываем.
        if ($reflection->isPrivate()) {
            return [];
        }

        try {
            $annotations = new Annotations(
                $reflection
            );
            return $annotations->asArray();
        } catch (ReflectorNotCommentedException $e) {
            return [];
        }
    }

    /**
     * Инициализировать Symfony PropertyInfoExtractor.
     *
     * @return PropertyInfoExtractor
     */
    private function initSymfonyReflector(): PropertyInfoExtractor
    {
        // a full list of extractors is shown further below
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        // list of PropertyListExtractorInterface (any iterable)
        $listExtractors = [$reflectionExtractor];

        // list of PropertyTypeExtractorInterface (any iterable)
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];

        // list of PropertyDescriptionExtractorInterface (any iterable)
        $descriptionExtractors = [$phpDocExtractor];

        // list of PropertyAccessExtractorInterface (any iterable)
        $accessExtractors = [$reflectionExtractor];

        // list of PropertyInitializableExtractorInterface (any iterable)
        $propertyInitializableExtractors = [$reflectionExtractor];

        return new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors,
            $propertyInitializableExtractors
        );
    }

    /**
     * Установить значение свойства объекта.
     *
     * @param mixed  $object   Объект.
     * @param string $property Свойство.
     * @param mixed  $value    Значение.
     *
     * @throws ReflectionException
     */
    private function setPropertyValue($object, string $property, $value): void
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

    /**
     * Получить значение свойства объекта.
     *
     * @param mixed  $object   Объект.
     * @param string $property Свойство.
     *
     * @return mixed
     * @throws ReflectionException
     */
    private function getPropertyValue($object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);

        return $reflection_property->getValue($object);
    }

    /**
     *
     * Свойсто объекта равно null?
     *
     * @param mixed  $object   Объект.
     * @param string $property Свойство.
     *
     * @return boolean
     * @throws ReflectionException
     */
    private function isNullValueProperty($object, string $property): bool
    {
        $value = $this->getPropertyValue($object, $property);

        return $value === null;
    }

    /**
     * Получить все свойства класса.
     *
     * @param mixed $object Объект.
     *
     * @return array
     */
    private function getAllPropertiesClass($object): array
    {
        /** Результат. */
        $allProperties = [];

        try {
            $reflect = new ReflectionClass($object);
        } catch (ReflectionException $e) {
            return [];
        }

        // С приватными свойствами не работаем.
        $props = $reflect->getProperties(
            ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED
        );

        /** @var array $allProperties Все свойства класса. */
        foreach ($props as $property) {
            $allProperties[$property->getName()] = true;
        }

        return $allProperties;
    }
}
