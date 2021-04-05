<?php

namespace Local\ServiceProvider\CompilePasses;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ValidateServiceDefinitions
 * @package Local\ServiceProvider\CompilePasses
 *
 * @since 28.09.2020
 */
class ValidateServiceDefinitions implements CompilerPassInterface
{
    private const KNOWN_UNUSED_SERVICES = [
        'beberlei_metrics.util.buzz.curl' => true, // not cleaned out by bundle
        'beberlei_metrics.util.buzz.browser' => true, // not cleaned out by bundle
        'form.type.entity' => true, // breaks when loading the php file
        'debug.file_link_formatter.url_format' => true, // configured to by "string"
        'service_container' => true, // ContainerInterface but no factory
    ];

    /**
     * Look at *all* service definitions and validate that their classes exist.
     *
     * With autoconfigure but no autowire, we otherwise never see invalid class
     * names for listeners or such.
     *
     * This needs to be a compilar pass running at PassConfig::TYPE_BEFORE_REMOVING.
     * A command would only see the services that have not been removed from the
     * container.
     *
     * @inheritDoc
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (array_key_exists($serviceId, self::KNOWN_UNUSED_SERVICES)
                || $definition->isAbstract()
            ) {
                continue;
            }

            $class = $definition->getClass();
            if ($class) {
                $class = $container->getParameterBag()->resolveValue($class);

                if (!class_exists($class)
                    &&
                    ($definition->getFactory() === null || !interface_exists($class))
                ) {
                    throw new Exception(
                        sprintf(
                            'Service %s is configured to use the nonexistent class %s',
                            $serviceId,
                            $class
                        )
                    );
                }
            } elseif (!$definition->isSynthetic()) {
                throw new Exception(
                    sprintf('Service %s has no class', $serviceId)
                );
            }
        }
    }
}
