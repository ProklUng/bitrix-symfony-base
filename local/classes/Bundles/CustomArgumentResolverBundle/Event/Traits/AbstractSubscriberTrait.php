<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AbstractSubscriberTrait
 * Общие методы для подписчиков на события OnControllerRequest.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Traits
 *
 * @since 10.09.2020
 */
trait AbstractSubscriberTrait
{
    /**
     * @var ContainerInterface $container Сервис-контейнер.
     */
    private $container;

    /**
     * Всегда переопределять значение!
     *
     * @var integer $priority Приоритет события.
     */
    private static $priority = 10;

    /**
     * Инициализировать параметры. В данном случае контейнер.
     *
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function init(ContainerInterface $container) : self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['handle', static::$priority]
            ],
        ];
    }

    /**
     * Использует ли этот контроллер такой-то трэйт.
     *
     * @param ControllerEvent $event Объект события.
     * @param string          $trait Название трэйта.
     *
     * @return boolean
     */
    private function useTrait(
        ControllerEvent $event,
        string $trait
    ): bool {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return false;
        }

        // class_uses_recursive - Laravel helper.
        $traits = class_uses($controller[0]);
        if (!$traits || !in_array($trait, $traits, true)) {
            return false;
        }

        return true;
    }
}
