<?php

namespace Local\ServiceProvider\BaseFacade;

use Exception;
use Local\ServiceProvider\ServiceProvider;
use Mockery;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Class Facade
 * Базовый фасад для сервисов.
 * @package Local\ServiceProvider\BaseFacade
 *
 * @since 18.09.2020 Доработки.
 */
class Facade
{
    /** @var ContainerInterface $app The application instance being facaded. */
    protected static $app;

    /** @var array $resolvedInstance Локальные сервисы. Для заглушек. */
    protected static $resolvedInstance = [];

    /**
    * Boot.
    *
    * @noinspection PhpUndefinedMethodInspection
    */
    public static function boot() : void
    {
        static::$app = ServiceProvider::instance();
    }

    /**
     * Получить экземпляр сервиса из контейнера.
     *
     * @return mixed
     * @throws RuntimeException
     *
     * @since 18.09.2020
     */
    public static function instanceService()
    {
        // Название сервиса, к которому привязан фасад.
        $accessor = static::getFacadeAccessor();

        // Чтобы поддерживалось моканье.
        if (!empty(static::$resolvedInstance[$accessor])) {
            return static::$resolvedInstance[$accessor];
        }

        try {
            $service = static::$app->get($accessor);
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('Ошибка сервис контейнера: %s.', $e->getMessage())
            );
        }

        return $service;
    }

    /**
     * Ларавеловская подмена сервиса.
     *
     * @param mixed $mock
     */
    public static function swapService($mock): void
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = $mock;
    }

    /**
     * Нативная runtime установка сервиса. Для заглушек.
     *
     * @param string $functionName
     * @param mixed $param
     */
    public static function swap(string $functionName, $param = null): void
    {
        static::$resolvedInstance[static::getFacadeAccessor()][$functionName] = $param;
    }

    /**
     * Очистить все установленно-локально сервисы.
     */
    public static function clearResolvedInstance(): void
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = [];
    }

    /**
     * Get the application instance behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeApplication()
    {
        return static::$app;
    }

    /**
     * Convert the facade into a Mockery spy.
     *
     * @return MockInterface | null
     */
    public static function spy(): ?MockInterface
    {
        if (!static::isMock()) {
            $class = static::getMockableClass();

            return tap($class ? Mockery::spy($class) : Mockery::spy(), function ($spy) {
                static::swap($spy);
            });
        }

        return null;
    }

    /**
     * Initiate a partial mock on the facade.
     *
     * @return MockInterface
     */
    public static function partialMock(): MockInterface
    {
        $name = static::getFacadeAccessor();

        $mock = static::isMock()
            ? static::$resolvedInstance[$name]
            : static::createFreshMockInstance();

        return $mock->makePartial();
    }

    /**
     * Initiate a mock expectation on the facade.
     *
     * @return mixed
     */
    public static function shouldReceive()
    {
        $name = static::getFacadeAccessor();

        $mock = static::isMock()
            ? static::$resolvedInstance[$name]
            : static::createFreshMockInstance();

        return $mock->shouldReceive(...func_get_args());
    }

    /**
     * Сервис фасада. Для наследования.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return '';
    }

    /**
     * Determines whether a mock is set as the instance of the facade.
     *
     * @return bool
     */
    protected static function isMock(): bool
    {
        $name = static::getFacadeAccessor();

        return static::$resolvedInstance[$name] !== null &&
            static::$resolvedInstance[$name] instanceof MockInterface;
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     * @return MockInterface
     */
    protected static function createFreshMockInstance(): MockInterface
    {
        return tap(static::createMock(), function ($mock) {
            static::swapService($mock);

            $mock->shouldAllowMockingProtectedMethods();
        });
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     * @return MockInterface
     */
    protected static function createMock(): MockInterface
    {
        $class = static::getMockableClass();

        return $class ? Mockery::mock($class) : Mockery::mock();
    }

    /**
     * Get the mockable class for the bound instance.
     *
     * @return string
     */
    protected static function getMockableClass(): string
    {
        if ($root = self::$app->get(static::getFacadeAccessor())) {
            return get_class($root);
        }

        return '';
    }

    /**
     * Доступ к статической переменной сервиса.
     *
     * @param string $variable Статическая переменная.
     *
     * @return mixed
     */
    public static function __getStatic(string $variable)
    {
        // Название сервиса, к которому привязан фасад.
        $accessor = static::getFacadeAccessor();
        /** Сервис из контейнера. */
        try {
            $service = static::$app->get($accessor);
        } catch (Exception $e) {
            static::$app->get('die.screen')->die('Service container: ' . $e->getMessage());
            return null;
        }

        if (!empty($service::${$variable})) {
            return $service::${$variable};
        }

        return null;
    }

    /**
     * Статический фасад.
     *
     * @param string $method Сервис.
     * @param mixed $args Аргументы.
     *
     * @return array|mixed|object
     * @throws RuntimeException
     *
     * @internal  Пример вызова: Wordpress::get_post_thumbnail_id().
     * Вызовется аналог функции get_post_thumbnail_id.
     *
     */
    public static function __callStatic(string $method, $args = null)
    {
        if (!static::$app) {
            throw new RuntimeException('Facades not initialized.');
        }

        // Название сервиса, к которому привязан фасад.
        $accessor = static::getFacadeAccessor();

        // Мокинг через встроенную систему базового фасада.
        if (static::$resolvedInstance[$accessor] instanceof MockInterface) {
            return static::$resolvedInstance[$accessor]->$method(...$args);
        }

        // Нативный мокинг. Сначала ищем в массиве заглушек.
        if (!empty(static::$resolvedInstance[$accessor][$method])) {
            $callable = static::$resolvedInstance[$accessor][$method];
            // Callable?
            if (is_callable($callable)) {
                return $callable();
            }

            return static::$resolvedInstance[$accessor][$method];
        }

        /** Сервис из контейнера. */
        try {
            $service = static::$app->get($accessor);
        } catch (Exception $e) {
            static::$app->get('die.screen')->die(
                'Service container: ' . $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>'
            );

            return null;
        }

        if (!method_exists($service, $method)) {
            throw new RuntimeException(
                sprintf('Метод %s не найден.', $method)
            );
        }

        return $service->$method(...$args);
    }
}
