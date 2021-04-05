<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\InstagramParserRapidApiBundle\DependencyInjection
 *
 * @since 04.12.2020
 *
 * @psalm-suppress PossiblyUndefinedMethod
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rapid_api_instagram_parser');
        $rootNode    = $treeBuilder->getRootNode();

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
                ->booleanNode('mock')->defaultValue(false)->end()
                ->scalarNode('instagram_user_id')->end()
                ->scalarNode('instagram_user_name')->defaultValue('')->end()
                ->scalarNode('rapid_api_key')->end()
                ->scalarNode('fixture_response_path')->defaultValue(
                    '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/response.txt'
                )->end()
                ->scalarNode('fixture_user_path')->defaultValue(
                    '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/user.txt'
                )->end()
                ->scalarNode('cache_path')->defaultValue('cache/instagram-parser')->end()
                ->scalarNode('cache_ttl')->defaultValue(86400)->end()
                ->scalarNode('cache_user_data_ttl')->defaultValue(31536000)->end()
            ->end();

        return $treeBuilder;
    }
}
