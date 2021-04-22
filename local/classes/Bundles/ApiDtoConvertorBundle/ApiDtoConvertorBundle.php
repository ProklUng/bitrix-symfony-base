<?php

namespace Local\Bundles\ApiDtoConvertorBundle;

use Local\Bundles\ApiDtoConvertorBundle\DependencyInjection\ApiExtension;
use Local\Bundles\ApiDtoConvertorBundle\DependencyInjection\BaseDTOInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ApiDtoConvertorBundle
 * @package Local\Bundles\ApiDtoConvertorBundle
 *
 * @since 04.11.2020
 */
class ApiDtoConvertorBundle extends Bundle
{
    /**
     * @return mixed
     */
    public function getContainerExtension()
    {
        return new ApiExtension();
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        // Игнорируемые при автовайринге классы - интерфейсы.
        $autowiringControllerParamsBag = $this->container->get('custom_arguments_resolvers.ignored.autowiring.controller.arguments');
        if ($autowiringControllerParamsBag === null) {
            throw new RuntimeException(
                'Dependency service custom_arguments_resolvers.ignored.autowiring.controller.arguments not found.'
            );
        }

        $autowiringControllerParamsBag->add(
            [BaseDTOInterface::class]
        );
    }
}
