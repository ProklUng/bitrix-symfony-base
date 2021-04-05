<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Local\Bundles\SymfonyMiddlewareBundle\DependencyInjection\CompilerPass\ControllerMiddlewarePass;
use Local\Bundles\SymfonyMiddlewareBundle\DependencyInjection\CompilerPass\GlobalMiddlewarePass;
use Local\Bundles\SymfonyMiddlewareBundle\DependencyInjection\CompilerPass\RouteMiddlewarePass;
use Local\Bundles\SymfonyMiddlewareBundle\Middleware\MiddlewareEnum;

/**
 * Class MiddlewareBundle
 * @package Local\Bundles\SymfonyMiddlewareBundle
 *
 * @see https://github.com/zholus/symfony-middleware-bundle
 */
class MiddlewareBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(MiddlewareInterface::class)
            ->addTag(MiddlewareEnum::ALIAS_SUFFIX);

        $container->registerForAutoconfiguration(GlobalMiddlewareInterface::class)
            ->addTag(MiddlewareEnum::GLOBAL_TAG);

        $container->addCompilerPass(new GlobalMiddlewarePass());
        $container->addCompilerPass(new ControllerMiddlewarePass());
        $container->addCompilerPass(new RouteMiddlewarePass());
    }
}
