<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('bitrix_iblock_element_validator');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->arrayNode('iblocks')
                        ->arrayPrototype()
                            ->children()
                                    ->scalarNode('id_iblock')->defaultValue(0)->end()
                                    ->scalarNode('code_property')->isRequired()->end()
                                    ->scalarNode('sanitize')->defaultValue('')->end()
                                    ->scalarNode('rule')->defaultValue('')->end()
                                    ->scalarNode('error_message')->defaultValue('')->end()
                                    ->scalarNode('optional_validator')->defaultValue(null)->end()

                                   ->end()
                            ->end()
                        ->end()
            ->end();

        return $treeBuilder;
    }
}
