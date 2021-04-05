<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Local\Bundles\CustomArgumentResolverBundle\Event\Traits\UseTraitChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class SetContainer
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 * @since 06.12.2020 Распространил действие на все контроллеры с трэйтом ContainerAwareTrait.
 */
class SetContainer implements OnControllerRequestHandlerInterface
{
    use ContainerAwareTrait;
    use UseTraitChecker;

    /**
     * Загнать сервис-контейнер в контроллер.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     */
    public function handle(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller) || !$event->isMasterRequest()) {
            return;
        }

        // Только для контроллеров и классов с трэйтом ContainerAwareTrait.
        if ($controller[0] instanceof AbstractController
            ||
            $this->useTrait($event, ContainerAwareTrait::class)
        ) {
            // Установить сервис-контейнер.
            $controller[0]->setContainer($this->container);
        }
    }
}
