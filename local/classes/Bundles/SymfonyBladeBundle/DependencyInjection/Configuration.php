<?php

namespace Local\Bundles\SymfonyBladeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\SymfonyBladeBundle\DependencyInjection
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('blade');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('baseViewPath')->defaultValue('%kernel.project_dir%/local/views')->end()
            ->scalarNode('cachePath')->defaultValue('%kernel.project_dir%/bitrix/cache/blade')->end()
            ->booleanNode('readonly')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
