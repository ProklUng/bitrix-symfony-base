<?php

namespace Local\Bundles\StaticPageMakerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\StaticPageMakerBundle\DependencyInjection
 *
 * @since 23.01.2021
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('static_page_maker');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('seo_iblock_id')->defaultValue(0)->end()
                ->booleanNode('set_last_modified_header')->defaultValue(false)->end()
            ->end();

        return $treeBuilder;
    }
}
