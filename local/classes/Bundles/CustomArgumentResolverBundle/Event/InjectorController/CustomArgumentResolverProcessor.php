<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\InjectorController;

use Closure;
use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Service\ResolversDependency\ResolveDependencyMakerContainerAware;
use Local\Bundles\CustomArgumentResolverBundle\Service\Utils\IgnoredAutowiringControllerParamsBag;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class CustomArgumentResolverProcessor
 * Общий процессор.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event
 *
 * @since 06.09.2020 Clearing.
 * @since 28.09.2020 Чистка. Выпилил трэйт.
 * @since 29.09.2020 Доработка в сторону инжекции зависимостей от Symfony.
 * @since 30.09.2020 Доработка.
 * @since 08.10.2020 Сеттер контейнера. Контейнер устанавливается снаружи.
 * @since 12.10.2020 Разрешитель зависимостей заменен на ResolveDependencyMakerContainerAware.
 * @since 28.10.2020 Обработка значений по умолчанию. Серьезный рефакторинг.
 * @since 31.10.2020 Фикс ошибки рефлексии параметра, не имеющего значения по умолчанию.
 * @since 08.11.2020 Обработка классов-исключений из автовязи (DTO, например).
 * @since 03.12.2020 Поддержка аттрибутов, как без $, так и с ним. В routes.yaml можно писать
 * как угодно. Для совместимости с нативным Symfony.
 * @since 02.02.2021 Выпиливание ResolveParamsFromContainer.
 */
class CustomArgumentResolverProcessor implements InjectorControllerInterface
{
    use ContainerAwareTrait;

    /**
     * @var ResolveDependencyMakerContainerAware $resolveDependencyMaker Разрешитель зависимостей.
     */
    private $resolveDependencyMaker;

    /**
     * @var IgnoredAutowiringControllerParamsBag $autowiringControllerParamsBag Игнорируемые при автовайринге классы
     *  (учитывя наследование).
     */
    private $autowiringControllerParamsBag;

    /**
     * CommonProcessor constructor.
     *
     * @param ResolveDependencyMakerContainerAware $resolveDependencyMaker
     * @param IgnoredAutowiringControllerParamsBag $autowiringControllerParamsBag
     */
    public function __construct(
        ResolveDependencyMakerContainerAware $resolveDependencyMaker,
        IgnoredAutowiringControllerParamsBag $autowiringControllerParamsBag
    ) {
        $this->resolveDependencyMaker = $resolveDependencyMaker;
        $this->autowiringControllerParamsBag = $autowiringControllerParamsBag;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function inject(ControllerEvent $event) : ControllerEvent
    {
        return $this->injectArgumentsToController($event);
    }

    /**
     * Инжекция зависимостей в контроллер.
     *
     * @param ControllerEvent $event
     *
     * @return ControllerEvent
     *
     * @throws Exception
     *
     * @since 06.09.2020 Рефакторинг в сторону упрощения. Доработка разрешения переменных.
     * @since 29.09.2020 Доработка в сторону инжекции зависимостей от Symfony.
     * @since 03.10.2020 Долгожданное исправление ошибки с разрешением сервисов из контейнера.
     * @since 12.10.2020 Разрешитель зависимостей заменен на ResolveDependencyMakerContainerAware.
     */
    private function injectArgumentsToController(ControllerEvent $event): ControllerEvent
    {
        /** @var array $arArguments Аргументы контроллера. */
        try {
            $arArguments = $this->getArguments(
                $event->getRequest(),
                $event->getController(),
            );
        } catch (ReflectionException $e) {
            /** @psalm-suppress PossiblyNullReference */
            $this->container->get('die_text')->die(
                'Ошибка в инжекции данных в конструктор контроллера ' . static::class
            );

            return $event; // Для тестов.
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
            $resolvedFromContainer = $this->resolve($argItem);
            if ($resolvedFromContainer !== null) {
                $event->getRequest()->attributes->set($param, $resolvedFromContainer);
                continue;
            }

            // Всегда в начале пытаться достать из контейнера.
            // Не вынес в метод, потому что дело касается только основного цикла инжекции.
            if ($this->container->has($argItem) // На всякий случай!
                &&
                !is_object($event->getRequest()->attributes->get($param))
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
     * @param array $array
     *
     * @return array
     *
     * @since 28.10.2020
     */
    private function resolveParamsInArrayRecursively(array $array) : array
    {
        $result = [];

        foreach ($array as $param => $argItem) {
            if (is_array($argItem)) {
                $result[$param] = $this->resolveParamsInArrayRecursively($argItem);
                continue;
            }

            if (is_string($argItem)) {
                // Ресолвинг всего чего можно из контейнера.
                $resolvedFromContainer = $this->resolve($argItem);
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
    private function compareArrayByKeys(
        array $arTypesArguments,
        array $arArguments
    ) : array {
        $arResult = [];
        foreach ($arTypesArguments as $key => $item) {
            // Request нужно исключить!
            if ((!array_key_exists($key, $arArguments) || !$arArguments[$key])
                &&
                $item !== 'Symfony\Component\HttpFoundation\Request') {
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
    private function getArguments(Request $request, $controller): array
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
     * @throws ReflectionException
     *
     * @since 11.09.2020 Доработка: интерфейсы пропускать.
     * @since 28.10.2020 Обработка значений по умолчанию.
     * @since 31.10.2020 Фикс ошибки рефлексии параметра, не имеющего значения по умолчанию.
     */
    private function getTypesArguments($controller) : array
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
            if (!$class->isInterface() && $class->isAbstract()) {
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
     * @throws ReflectionException
     */
    private function reflectionController($controller)
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
    private function doGetArguments(Request $request, array $parameters): array
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

    /**
     * Разрешить все, что можно из контейнера.
     *
     * @param mixed $argItem Аргумент.
     *
     * @return mixed
     *
     */
    private function resolve($argItem)
    {
        if (!$argItem || is_object($argItem) || is_array($argItem)) {
            return $argItem;
        }

        $resolvedVariable = false;

        if (strpos($argItem, '%') === 0) {
            $containerVar = str_replace('%', '', $argItem);

            // Есть такой параметр в контейнере - действуем.
            if ($this->container->hasParameter($containerVar)) {
                $resolvedVarValue = $this->container->getParameter($containerVar);
                $resolvedVariable = true;

                if ($this->container->has((string)$resolvedVarValue)) {
                    $resolvedVarValue = '@' . $resolvedVarValue;
                }

                $argItem = $resolvedVarValue;
            }

            // Продолжаем дальше, потому что в переменной может быть алиас сервиса.
        }

        // Если использован алиас сервиса, то попробовать получить его из контейнера.
        if (strpos($argItem, '@') === 0) {
            $resolvedService = $this->container->get(
                ltrim($argItem, '@')
            );

            if ($resolvedService !== null) {
                return $resolvedService;
            }
        }

        return !$resolvedVariable ? null : $argItem;
    }
}
