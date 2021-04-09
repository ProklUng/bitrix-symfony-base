<?php

namespace Local\Bundles\BitrixDatabaseBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('bitrix_database');

        $rootNode = $treeBuilder->getRootNode();

        // @phpstan-ignore-next-line
        $rootNode
            // @phpstan-ignore-next-line
            ->children()
                // @phpstan-ignore-next-line
                ->arrayNode('bitrix_database_bundle_fixture_path')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('structure_project')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
