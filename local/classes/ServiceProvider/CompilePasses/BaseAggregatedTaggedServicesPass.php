<?php

namespace Local\ServiceProvider\CompilePasses;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class BaseAggregatedTaggedServicesPass
 * Базовый кастомный Compile Pass.
 * @package Local\ServiceProvider\CompilePasses
 *
 * @since 26.09.2020
 * @since 06.11.2020 Добавление к уже существующим параметрам, а не перезаписывание. Позволяет бандлам
 * подмешивать свои добавления.
 */
class BaseAggregatedTaggedServicesPass implements CompilerPassInterface
{
    /** @var string $tag Искомый сервисный тэг. */
    private $tag;

    /**
     * @var string $nameSectionParameterBag Название раздела в ParameterBag, где
     * сохранятся результаты.
     */
    private $nameSectionParameterBag;

    /**
     * BaseAggregatedTaggedServicesPass constructor.
     *
     * @param string $tag                     Искомый сервисный тэг.
     * @param string $nameSectionParameterBag Название раздела в ParameterBag.
     */
    public function __construct(
        string $tag,
        string $nameSectionParameterBag
    ) {
        $this->tag = $tag;
        $this->nameSectionParameterBag = $nameSectionParameterBag;
    }

    /**
     * Движуха.
     *
     * @param ContainerBuilder $container Контейнер.
     *
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds(
            $this->tag
        );

        if (empty($taggedServices)) {
            return;
        }

        $params = $container->hasParameter($this->nameSectionParameterBag) ?
            $container->getParameter($this->nameSectionParameterBag)
            : [];

        $container->setParameter(
            $this->nameSectionParameterBag,
            array_merge($params, $taggedServices)
        );
    }
}
