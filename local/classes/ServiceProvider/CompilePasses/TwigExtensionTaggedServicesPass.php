<?php

namespace Local\ServiceProvider\CompilePasses;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class TwigExtensionTaggedServicesPass
 * Обработка сервисов с тэгом twig.extension.
 * @package Local\ServiceProvider\CompilePasses
 *
 * @since 11.10.2020
 * @since 06.11.2020 Добавление к уже существующим параметрам, а не перезаписывание. Позволяет бандлам
 * добавлять свои Twig Extension.
 */
class TwigExtensionTaggedServicesPass implements CompilerPassInterface
{
    /** @const string TAG_TWIG_EXTENSION Тэг сервисов, расширяющих Twig. */
    protected const TAG_TWIG_EXTENSION = 'twig.extension';

    /** @const string TWIG_EXTENSION_PARAM_CONTAINER Название переменной в контейнере. */
    protected const TWIG_EXTENSION_PARAM_CONTAINER = '_twig_extension';

    /**
     * Движуха.
     *
     * @param ContainerBuilder $container Контейнер.
     *
     * @return void
     * @throws Exception Ошибки контейнера.
     */
    public function process(ContainerBuilder $container) : void
    {
        $taggedServices = $container->findTaggedServiceIds(
            self::TAG_TWIG_EXTENSION
        );

        if (empty($taggedServices)) {
            return;
        }

        $params = $container->hasParameter(self::TWIG_EXTENSION_PARAM_CONTAINER) ?
            $container->getParameter(self::TWIG_EXTENSION_PARAM_CONTAINER)
            : [];

        // Сервисы автозапуска.
        $container->setParameter(
            self::TWIG_EXTENSION_PARAM_CONTAINER,
            array_merge($params, $taggedServices)
        );
    }
}
