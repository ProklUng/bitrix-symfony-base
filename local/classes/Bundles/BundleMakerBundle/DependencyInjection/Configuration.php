<?php

namespace Local\Bundles\BundleMakerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\BundleMakerBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('netbrothers_nbcsb');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('template_dir')
                    ->info('path to templates (default to directory `installation/templates` in bundle)')
                    ->defaultValue('default')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
