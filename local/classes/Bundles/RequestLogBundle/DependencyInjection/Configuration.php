<?php

namespace Local\Bundles\RequestLogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\RequestLogBundle\DependencyInjection
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mroca_request_log');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('bitrix_uri_list')
            ->useAttributeAsKey('code')
            ->info("Пример: '/^\/clubs\/$/', '/^\/about\/$/', '/^\/xxx\/$/'")
            ->prototype('scalar')->end()
            ->defaultValue([])
            ->end()
            ->scalarNode('mocks_dir')->cannotBeEmpty()->info('The generated log files path')->defaultValue('%kernel.project_dir%/../../logs/mocks/')->end()
            ->scalarNode('mocks_dir_commands')->cannotBeEmpty()->defaultValue('/logs/mocks')->end()
            ->booleanNode('hash_query_params')->info('Transform query params string into hash in the file names')->defaultFalse()->end()
            ->booleanNode('mock_request')->info('Мокать запросы при существовании фикстуры')->defaultTrue()->end()
            ->booleanNode('use_indexed_associative_array')->info('Use indexed foo[0]=bar format instead of foo[]=bar')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}