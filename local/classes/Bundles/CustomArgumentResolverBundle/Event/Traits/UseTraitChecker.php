<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Traits;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Trait UseTraitChecker
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Traits
 *
 * @since 05.12.2020
 */
// @phpstan-ignore-next-line
trait UseTraitChecker
{
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
