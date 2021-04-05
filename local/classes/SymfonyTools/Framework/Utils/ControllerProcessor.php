<?php

namespace Local\SymfonyTools\Framework\Utils;

use Closure;
use Local\ServiceProvider\Utils\IgnoredAutowiringControllerParamsBag;
use Local\SymfonyTools\ArgumentsResolvers\Supply\ResolveParamsFromContainer;
use Local\SymfonyTools\Framework\Utils\ResolverDependency\ResolveDependencyMakerContainerAware;
use Psr\Container\ContainerInterface;
use Local\SymfonyTools\Framework\Exceptions\ArgumentsControllersException;
use Local\SymfonyTools\Framework\Utils\ResolverDependency\ResolveDependencyMaker;
use Local\SymfonyTools\Framework\Interfaces\InjectorControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;

/**
 * Class ControllerProcessor
 * Процессор контроллеров.
 * @package Local\SymfonyTools\Framework\Utils
 *
 * @since 05.09.2020
 * @since 10.09.2020 PSR-2 форматирование.
 * @since 31.10.2020 Фикс ошибки рефлексии параметра, не имеющего значения по умолчанию.
 * @since 08.11.2020 Обработка классов-исключений из автовязи (DTO, например).
 * @since 03.12.2020 Поддержка аттрибутов, как без $, так и с ним. В routes.yaml можно писать
 * как угодно. Для совместимости с нативным Symfony.
 */
class ControllerProcessor implements InjectorControllerInterface
{
    /**
     * @var ResolveDependencyMaker $resolveDependencyMaker Разрешитель зависимостей.
     */
    private $resolveDependencyMaker;

    /**
     * @var ResolveParamsFromContainer $resolveParamsFromContainer Ресолвер параметров и сервисов из контейнера.
     */
    private $resolveParamsFromContainer;

    /**
     * @var ContainerInterface $container Сервис-контейнер.
     */
    private $container;

    /**
     * @var IgnoredAutowiringControllerParamsBag $autowiringControllerParamsBag Игнорируемые при автовайринге классы
     *  (учитывя наследование).
     */
    private $autowiringControllerParamsBag;

    /**
     * ControllerProcessor constructor.
     *
     * @param ContainerInterface                   $container                     Сервис-контейнер.
     * @param ResolveDependencyMakerContainerAware $dependencyMaker               Разрешитель зависимостей.
     * @param ResolveParamsFromContainer           $resolveParamsFromContainer    Разрешитель зависимостей переменных и сервисов.
     * @param IgnoredAutowiringControllerParamsBag $autowiringControllerParamsBag Игнорируемые при автовайринге классы.
     */
    public function __construct(
        ContainerInterface $container,
        ResolveDependencyMakerContainerAware $dependencyMaker,
        ResolveParamsFromContainer $resolveParamsFromContainer,
        IgnoredAutowiringControllerParamsBag $autowiringControllerParamsBag
    ) {
        $this->container = $container;
        $this->resolveDependencyMaker = $dependencyMaker;
        $this->resolveParamsFromContainer = $resolveParamsFromContainer;
        $this->autowiringControllerParamsBag = $autowiringControllerParamsBag;
    }

    /**
     * Инжекция зависимостей в контроллер.
     *
     * @param ControllerEvent $event Событие.
     *
     * @return ControllerEvent
     *
     * @throws ArgumentsControllersException Ошибки инжекции.
     * @throws ReflectionException           Ошибки рефлексии.
     *
     * @since 06.09.2020 Рефакторинг в сторону упрощения.
     * @since 09.11.2020 Поддержка заданных имплицитно классов, игнорируемых при обработке.
     */
    public function inject(ControllerEvent $event): ControllerEvent
    {
        /** @var array $arArguments Аргументы контроллера. */
        try {
            $arArguments = $this->getArguments(
                $event->getRequest(),
                $event->getController()
            );
        } catch (ReflectionException $e) {
            throw new ArgumentsControllersException(
                'Ошибка в инжекции данных в конструктор контроллера ' . static::class
            );
        }

        try {
            $arTypesArguments = $this->getTypesArguments($event->getController());
        } catch (ReflectionException $e) {
            $arTypesArguments = [];
        }

        // Аргументы, не указанные в конфиге, но полученные рефлексией.
        $arAutowiredServices = $this->compareArrayByKeys($arTypesArguments, $arArguments);

        // Подмешать в результат.
        $arArguments = array_merge($arArguments, $arAutowiredServices);

        // Загнать аргументы в контроллер.
        foreach ($arArguments as $param => $argItem) {
            if (is_object($argItem)) {
                $event->getRequest()->attributes->set($param, $argItem);
                continue;
            }

            // Массив.
            if (is_array($argItem)) {
                $event->getRequest()->attributes->set(
                    $param,
                    $this->resolveParamsInArrayRecursively($argItem)
                );
                continue;
            }

            // Ресолвинг всего чего можно из контейнера.
            $resolvedFromContainer = $this->resolveParamsFromContainer->resolve($argItem);
            if ($resolvedFromContainer !== null) {
                $event->getRequest()->attributes->set($param, $resolvedFromContainer);
                continue;
            }

            // Всегда в начале пытаться достать из контейнера.
            // Не вынес в метод, потому что дело касается только основного цикла инжекции.
            if (!is_object($event->getRequest()->attributes->get($param)) // На всякий случай!
                &&
                $this->container->has($argItem)
            ) {
                $event->getRequest()->attributes->set($param, $this->container->get($argItem));
                continue;
            }

            // Крайний случай. Разрешить зависимости во всю рекурсивную глубину.
            if (class_exists($argItem)) {

                /**
                 * Игнорировать autowiring классов для некоторых исключений (DTO),
                 * указанных в массиве ignoredBaseClasses.
                 *
                 * @since 08.11.2020
                 */
                if ($this->autowiringControllerParamsBag->isIgnoredClass($argItem)) {
                    continue;
                }

                $resolved = $this->resolveDependencyMaker->resolveDependencies($argItem);
                $event->getRequest()->attributes->set($param, $resolved);
                continue;
            }

            // Значения по умолчанию. Когда ничего не получилось.
            if ($argItem !== null) {
                $event->getRequest()->attributes->set($param, $argItem);
            }
        }

        return $event;
    }

    /**
     * Массив со значениями по умолчанию обработать рекурсивно. Попутно разрешить
     * сервисы из контейнера. Но игнорить классы как параметры.
     *
     * @param array $array Параметры в виде массива.
     *
     * @return array
     *
     * @since 28.10.2020
     */
    protected function resolveParamsInArrayRecursively(array $array) : array
    {
        $result = [];

        foreach ($array as $param => $argItem) {
            if (is_array($argItem)) {
                $result[$param] = $this->resolveParamsInArrayRecursively($argItem);
                continue;
            }

            if (is_string($argItem)) {
                // Ресолвинг всего чего можно из контейнера.
                $resolvedFromContainer = $this->resolveParamsFromContainer->resolve($argItem);
                $argItem = $resolvedFromContainer ?? $argItem;
            }

            $result[$param] = $argItem;
        }

        return $result;
    }

    /**
     * Вычленить аргументы, отсутствующие в конфиге. Request исключаем.
     *
     * @param array $arTypesArguments Типы всех аргументов контроллера.
     * @param array $arArguments      Аргументы, переданные через конфиг.
     *
     * @return array
     */
    protected function compareArrayByKeys(
        array $arTypesArguments,
        array $arArguments
    ): array {
        $arResult = [];

        foreach ($arTypesArguments as $key => $item) {
            // Есть ли такой сервис в сервис-контейнере?
            // Но только, если не передали параметр снаружи.
            if (empty($arArguments[$key])
                &&
                $this->container->has($item)
            ) {
                $arResult[$key] = $item;
                continue;
            }

            // Request нужно исключить!
            if (empty($arArguments[$key])
                &&
                $item !== 'Symfony\Component\HttpFoundation\Request'
            ) {
                $arResult[$key] = $item;
            }
        }

        return $arResult;
    }

    /**
     * Получить аргументы контроллера.
     *
     * @param Request $request    Request.
     * @param mixed   $controller Контроллер.
     *
     * @return array
     * @throws ReflectionException
     */
    protected function getArguments(Request $request, $controller): array
    {
        $reflection = $this->reflectionController($controller);

        return $this->doGetArguments($request, $reflection->getParameters());
    }

    /**
     * Собрать типы аргументов. Для классов: параметр контроллера - название класса.
     *
     * @param mixed $controller Контроллер.
     *
     * @return array
     * @throws ReflectionException Ошибки рефлексии.
     *
     * @since 28.10.2020 Обработка значений по умолчанию.
     * @since 31.10.2020 Фикс ошибки рефлексии параметра, не имеющего значения по умолчанию.
     */
    protected function getTypesArguments($controller) : array
    {
        $arResult = [];

        $reflection = $this->reflectionController($controller);

        foreach ($reflection->getParameters() as $param) {
            $class = $param->getClass();
            if (!$class) {
                // Обработка значений по умолчанию.
                try {
                    $defaultValue = $param->getDefaultValue();
                } catch (ReflectionException $e) {
                    $defaultValue = null;
                }

                if ($defaultValue !== null) {
                    $arResult[$param->getName()] = $defaultValue;
                }

                continue;
            }

            // Не дать проскочить абстрактным классам.
            if (!$class
                ||
                (!$class->isInterface() && $class->isAbstract())
            ) {
                continue;
            }

            $arResult[$param->name] = $class->name;
        }

        return $arResult;
    }

    /**
     * Рефлексия контроллера.
     *
     * @param mixed $controller Контроллер.
     *
     * @return ReflectionFunction|ReflectionMethod
     * @throws ReflectionException Ошибки рефлексии.
     */
    protected function reflectionController($controller)
    {
        if (is_array($controller)) {
            $reflection = new ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof Closure) {
            $reflection = new ReflectionObject($controller);
            $reflection = $reflection->getMethod('__invoke');
        } else {
            $reflection = new ReflectionFunction($controller);
        }

        return $reflection;
    }

    /**
     * Сама механика получения аргументов.
     *
     * @param Request $request     Запрос.
     * @param array   $parameters  Параметры.
     *
     * @return array
     *
     * @since 03.12.2020 Поддержка аттрибутов, как без $, так и с ним. В routes.yaml можно писать
     * как угодно. Для совместимости с нативным Symfony.
     */
    protected function doGetArguments(Request $request, array $parameters): array
    {
        $attributes = $request->attributes->all();
        $arguments = [];

        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)
                ||
                array_key_exists('$' . $param->name, $attributes)
            ) {
                $arguments[$param->name] = $attributes[$param->name] ?? $attributes['$' . $param->name];
            }
        }

        return $arguments;
    }
}