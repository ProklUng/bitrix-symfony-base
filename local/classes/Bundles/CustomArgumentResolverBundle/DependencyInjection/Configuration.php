<?php

namespace Local\Bundles\CustomArgumentResolverBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package ocal\Bundles\CustomArgumentResolverBundle\DependencyInjection
 *
 * @since 04.12.2020
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('custom_arguments_resolvers');
        $rootNode    = $treeBuilder->getRootNode();

        \assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
            ->arrayNode('defaults')
            ->useAttributeAsKey('name')
                ->prototype('boolean')->end()
                ->defaultValue([
                    'enabled' => false,
                ])
                ->end()
            ->end()

            ->children()
            ->arrayNode('params')
                ->children()
                    ->booleanNode('process_only_non_service_controller')
                    ->defaultTrue()->end()
                    ->arrayNode('classes_controllers')
                    ->scalarPrototype()->end()
                ->end()
                    ->arrayNode('disabled_resolvers')
                    ->scalarPrototype()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
