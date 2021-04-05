<?php declare(strict_types=1);

namespace Local\Bundles\CustomArgumentResolverBundle;

use Local\Bundles\CustomArgumentResolverBundle\DependencyInjection\CompilerPass\RemoveServices;
use Local\Bundles\CustomArgumentResolverBundle\DependencyInjection\CustomArgumentResolverBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class CustomArgumentResolver
 * @package Local\Bundles\CustomArgumentResolverBundle
 *
 * @since 04.12.2020
 */
class CustomArgumentResolverBundle extends Bundle
{
    /**
     * @return CustomArgumentResolverBundleExtension
     */
    public function getContainerExtension(): CustomArgumentResolverBundleExtension
    {
        return new CustomArgumentResolverBundleExtension;
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);

        $removeDisabledService = new RemoveServices();

        $container->addCompilerPass(
            $removeDisabledService
        );
    }
}
