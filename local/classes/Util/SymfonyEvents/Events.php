<?php

namespace Local\Util\SymfonyEvents;

use Illuminate\Support\Collection;
use ReflectionException;
use ReflectionMethod;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Events
 * @package Local\Util\SymfonyEvents
 */
class Events
{
    /** @const string CONFIG_FILE Конфиг. */
    protected const CONFIG_FILE = '/local/configs/events.yaml';

    /** @var array $arConfig Конфигурация, загруженная из YAML файла. */
    protected static $arConfig;
    /** @var Collection | null $listenersCollection Коллекция слушателей событий. */
    private static $listenersCollection;

    /** @var EventDispatcher $obDispatcher */
    private static $obDispatcher;

    /**
     * Events constructor.
     *
     * @param string $fileName Файл конфигурации.
     */
    public function __construct(string $fileName = self::CONFIG_FILE)
    {
        if (self::$arConfig === null) {
            self::$arConfig[$fileName] = Yaml::parseFile($_SERVER['DOCUMENT_ROOT'] . $fileName);
            self::$listenersCollection = collect(self::$arConfig[$fileName]['data']);
        }

        /** Инициализация Symfony EventDispatcher. */
        if (self::$obDispatcher === null) {
            self::$obDispatcher = new EventDispatcher();
        }
    }

    /**
     * Применение слушателей событий.
     *
     * @return mixed
     */
    public function applyListeners()
    {
        static::$listenersCollection->each(function ($arListenerData) {
            /** @var string $sMethodListener Метод слушателя. */
            $sMethodListener = !empty($arListenerData['method']) ? $arListenerData['method'] : 'action';
            /** Приоритет. */
            $iPriority = !empty($arListenerData['priority']) ? (int)$arListenerData['priority'] : 0;

            $isStaticMethod = $this->isStaticMethod($arListenerData['handler'], $sMethodListener);

            $handler = [$arListenerData['handler'], $sMethodListener];
            // Не статический метод.
            if (!$isStaticMethod) {
                $handler = [new $arListenerData['handler'], $sMethodListener];
            }

            self::$obDispatcher->addListener(
                $arListenerData['event'],
                $handler,
                $iPriority
            );
        });

        return self::$obDispatcher;
    }

    /**
     * Запустить событие.
     *
     * @param string $sEventName Событие.
     * @param mixed  $obParams   Объект-параметры.
     *
     * @return mixed|object|null
     */
    public function dispatch(string $sEventName, $obParams = null)
    {
        if (!$sEventName) {
            return null;
        }

        if ($obParams === null) {
            $obParams = new stdClass();
        }

        return self::$obDispatcher->dispatch($obParams, $sEventName);
    }

    /**
     * Декоратор добавления слушателя.
     *
     * @param string  $eventName Название события.
     * @param mixed   $listener  Слушатель.
     * @param integer $priority  Приоритет.
     *
     * @return void
     */
    public function addListener(string $eventName, $listener, int $priority = 0): void
    {
        self::$obDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Декоратор удаления слушателя события.
     *
     * @param string $eventName Название события.
     * @param mixed  $listener  Слушатель.
     *
     * @return void
     */
    public function removeListener(string $eventName, $listener) : void
    {
        self::$obDispatcher->removeListener($eventName, $listener);
    }

    /**
     * Это статический метод?
     *
     * @param string $class   Класс.
     * @param string $sMethod Метод.
     *
     * @return boolean
     */
    private function isStaticMethod(string $class, string $sMethod) : bool
    {
        try {
            $reflection = new ReflectionMethod($class, $sMethod);
        } catch (ReflectionException $e) {
            die('Слушатель события '.$class. ' не существует.');
        }

        return $reflection->isStatic();
    }
}
