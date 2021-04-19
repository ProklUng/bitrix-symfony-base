<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\InvalidConfigArgumentException;
use Local\Bundles\CustomArgumentResolverBundle\Event\InjectorController\InjectorControllerInterface;
use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class ResolverParamsController
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 11.09.2020
 * @since 03.12.2020 Выпилил фабрику процессоров. За ненадобностью - полгода показало, что метод себя
 * не оправдал. Плюс мелкие доработки. Нужно ли обрабатывать этот контроллер вынесено в отдельную функцию.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 * @since 05.01.2021 Рефакторинг.
 */
class ResolverParamsController implements OnControllerRequestHandlerInterface
{
    use ContainerAwareTrait;

    /**
     * @var InjectorControllerInterface $processor Процессор.
     */
    private $processor;

    /**
     * @var array $config Конфигурация.
     */
    private $config;

    /**
     * ResolverParamsController constructor.
     *
     * @param InjectorControllerInterface $processor Процессор.
     * @param array                       $config    Конфигурация.
     */
    public function __construct(
        InjectorControllerInterface $processor,
        array $config = []
    ) {
        $this->processor = $processor;
        $this->config = $config;
    }

    /**
     * Разрешить параметры контроллера.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!$event->isMasterRequest()) {
            return;
        }

        // На случай, если придут сведения о контроллере в виде контроллер::action.
        if (is_string($controller)) {
            if (strpos($controller, '::') !== false) {
                $controller = explode('::', $controller, 2);
            } else {
                // Invoked controller.
                try {
                    /** @psalm-suppress ArgumentTypeCoercion */
                    new ReflectionMethod($controller, '__invoke');
                    $controller = [$controller, '__invoke'];
                } catch (ReflectionException $e) {
                }
            }
        }

        if (!is_array($controller)) {
            return;
        }

        // Только для контроллеров.
        if ($this->needProcessController($controller[0])) {
            $this->processor->inject($event);
        }
    }

    /**
     * Нужно ли обрабатывать этот контроллер.
     *
     * @param string|object $controller Контроллер.
     *
     * @return boolean
     *
     * @throws InvalidConfigArgumentException Несуществующий класс в конфиге.
     *
     * @since 03.12.2020
     * @since 04.12.2020 Конфиги из Yaml настроек бандла.
     */
    private function needProcessController($controller) : bool
    {
        // Эксперимент: если класс объявлен сервисом, то положиться на нативные средства Symfony.
        if ($this->config['params']['process_only_non_service_controller']) {
            /**
             * @var string|object $class Класс контроллера.
             */
            $class = $controller;
            if (is_object($controller)) {
                $class = get_class($controller);
            }

            /** @psalm-suppress PossiblyInvalidArgument */
            if (!$class || $this->container->has($class)) {
                return false;
            }
        }

        // Проверка на соответствие класса одному из приведенных в конфиге
        // Учитывая наследование.
        foreach ((array)$this->config['params']['classes_controllers'] as $classController) {
            if (!class_exists($classController)) {
                throw new InvalidConfigArgumentException(
                    sprintf(
                        'Class %s from options section classes_controllers not exist.',
                        $classController
                    )
                );
            }

            if (is_subclass_of($controller, $classController)
                ||
                get_class($controller) === $classController
            ) {
                return true;
            }
        }

        return false;
    }
}
