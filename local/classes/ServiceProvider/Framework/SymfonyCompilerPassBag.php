<?php

namespace Local\ServiceProvider\Framework;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;
use Symfony\Component\HttpKernel\DependencyInjection\RemoveEmptyControllerArgumentLocatorsPass;
use Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass;
use Symfony\Component\Routing\DependencyInjection\RoutingResolverPass;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass;

/**
 * Class SymfonyCompilerPassBag
 * @package Local\ServiceProvider\Framework
 *
 * @since 04.04.2021
 */
class SymfonyCompilerPassBag extends AbstractSymfonyCompilerPassBag
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
            'pass' => RemoveEmptyControllerArgumentLocatorsPass::class,
            'phase' => PassConfig::TYPE_BEFORE_REMOVING,
        ],
        [
            'pass' => AddConstraintValidatorsPass::class,
        ],
    ];
}
