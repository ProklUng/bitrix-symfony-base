<?php

namespace Local\SymfonyTools\ArgumentsResolvers\Supply;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class ResolveParamsFromContainer
 * @package Local\SymfonyTools\ArgumentsResolvers\Supply
 *
 * @since 28.10.2020
 */
class ResolveParamsFromContainer
{
    use ContainerAwareTrait;

    /**
     * Разрешить все, что можно из контейнера.
     *
     * @param mixed $argItem Аргумент.
     *
     * @return mixed
     *
     */
    public function resolve($argItem)
    {
        if (!$argItem || is_object($argItem)) {
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
