<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class BootTraits
 * Bootable Traits.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 * @since 19.09.2020 Добавлена инициализация трэйтов.
 * @since 11.10.2020 Переработка.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class BootTraits implements OnControllerRequestHandlerInterface
{
    /** @var array $booted Загруженные методы трэйтов. */
    private $booted = [];

    /**
     * Обработчик события kernel.controller.
     *
     * Инициализация трэйтов контроллера. Вызов метода boot + название трэйта, если таковой существует.
     * (из Laravel)
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @since 10.09.2020
     * @since 11.10.2020 Переработка.
     */
    public function handle(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!$event->isMasterRequest()) {
            return;
        }

        $this->booted = [];

        /** @psalm-suppress InvalidArrayAccess */
        foreach (class_uses_recursive($controller[0]) as $trait) {
            // Загрузка (статический метод).
            $method = 'boot' . class_basename($trait);

            /** @psalm-suppress InvalidArrayAccess */
            if ($this->methodExist($controller[0], $method)) {
                forward_static_call([$controller[0], $method]);

                $this->booted[] = $method;
            }

            // Инициализация (динамический метод).
            $method = 'initialize' . class_basename($trait);

            if ($this->methodExist($controller[0], $method)) {
                /** @psalm-suppress InvalidArrayAccess */
                $controller[0]->{$method}();

                $this->booted[] = $method;
            }
        }
    }

    /**
     * Существует ли метод и был ли он уже загружен.
     *
     * @param mixed  $class  Класс.
     * @param string $method Метод.
     *
     * @since 11.10.2020
     *
     * @return boolean
     */
    private function methodExist($class, string $method) : bool
    {
        return method_exists($class, $method)
            && !in_array($method, $this->booted, true);
    }
}
