<?php

namespace Local\ServiceProvider\Framework;

use Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;
use Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass;
use Symfony\Component\Routing\DependencyInjection\RoutingResolverPass;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass;

/**
 * Class SymfonyCompilerPassBagLight
 * @package Local\ServiceProvider\Framework
 *
 * @since 04.04.2021
 */
class SymfonyCompilerPassBagLight extends AbstractSymfonyCompilerPassBag
{
    /**
     * @var array $standartCompilerPasses Пассы Symfony.
     */
    protected $standartCompilerPasses = [
        [
            'pass' => ControllerArgumentValueResolverPass::class,
        ],
        [
            'pass' => RegisterControllerArgumentLocatorsPass::class,
        ],
        [
            'pass' => RoutingResolverPass::class,
        ],
        [
            'pass' => SerializerPass::class,
        ],
        [
            'pass' => PropertyInfoPass::class,
        ],
        [
            'pass' => AddConstraintValidatorsPass::class,
        ],
    ];
}
