<?php

namespace Local\Bundles\BitrixUtilsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\BitrixUtilsBundle\DependencyInjection
 *
 * @since 07.12.2020
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bitrix_utils');
        $rootNode    = $treeBuilder->getRootNode();

        // @phpstan-ignore-next-line
        $rootNode
            // @phpstan-ignore-next-line
            ->children()
                ->arrayNode('modules')
                    ->prototype('scalar')

                ->end()
            ->end();

        return $treeBuilder;
    }
}
