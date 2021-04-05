<?php

namespace Local\Bundles\ApiExceptionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('m6web_api_exception');

        $rootNode = $treeBuilder->getRootNode();

        // @phpstan-ignore-next-line
        $rootNode
            // @phpstan-ignore-next-line
            ->children()
                // @phpstan-ignore-next-line
                ->booleanNode('stack_trace')->defaultValue(false)->end()
                // @phpstan-ignore-next-line
                ->booleanNode('match_all')->defaultValue(true)->end()
                // @phpstan-ignore-next-line
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('code')->defaultValue(0)->end()
                        // @phpstan-ignore-next-line
                        ->integerNode('status')->defaultValue(500)->end()
                        // @phpstan-ignore-next-line
                        ->scalarNode('message')->defaultValue('Internal server error')->end()
                        // @phpstan-ignore-next-line
                        ->arrayNode('headers')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                            // @phpstan-ignore-next-line
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('exceptions')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->integerNode('code')->end()
                            ->integerNode('status')->end()
                            ->scalarNode('message')->end()
                            ->arrayNode('headers')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
