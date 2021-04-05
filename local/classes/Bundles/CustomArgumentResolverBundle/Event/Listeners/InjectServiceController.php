<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class InjectServiceController
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 * @since 04.12.2020 В бандле.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class InjectServiceController implements OnControllerRequestHandlerInterface
{
    use ContainerAwareTrait;

    /**
     * Обработчик события kernel.controller.
     *
     * Если контроллер зарегистрирован как сервис - использовать его.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $event->getController();

        $action = '';
        if (is_array($controller)) {
            $controller = $controller[0];

            // Получение метода контроллера.
            $controllerParams = $event->getRequest()->attributes->get('_controller');

            // Если строка, то расщепить и получить action так.
            if (is_string($controllerParams)) {
                $params = explode('::', $controllerParams);
                $action = $params[1];
            }

            // Если массив, то воспользоваться уже готовым.
            // Иной способ инициализации роутов.
            if (is_array($controllerParams)) {
                // @phpstan-ignore-next-line
                $action = !empty($controllerParams[1]) ? $controllerParams[1] : '';
            }
        }

        // Если контроллер зарегистрирован как сервис - использовать его.
        $classController = get_class($controller);

        if ($this->container->has($classController)) {
            $controller = $this->container->get($classController);
            $event->setController([$controller, $action]);
        }
    }
}
