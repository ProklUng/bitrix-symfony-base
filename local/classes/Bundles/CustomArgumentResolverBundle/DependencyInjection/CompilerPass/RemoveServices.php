<?php

namespace Local\Bundles\CustomArgumentResolverBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class RemoveServices
 * Удалить листенеры, прописанные юзером в конфиге.
 * @package Local\Bundles\CustomArgumentResolverBundle\DependencyInjection\CompilerPass
 *
 * @since 05.12.2020
 */
class RemoveServices implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        $params = $container->getParameter('custom_arguments_resolvers');

        // @phpstan-ignore-next-line
        if (empty($params['params']['disabled_resolvers'])) {
            return;
        }

        $excludedServices = $params['params']['disabled_resolvers'];
        foreach ($excludedServices as $serviceId) {
            try {
                $container->findDefinition($serviceId);
            } catch (ServiceNotFoundException $e) {
                continue;
            }

            $container->removeDefinition($serviceId);
        }
    }
}
