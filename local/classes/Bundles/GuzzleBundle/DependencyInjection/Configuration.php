<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle\DependencyInjection;

use Local\Bundles\GuzzleBundle\DataCollector\GuzzleCollector;
use GuzzleHttp\MessageFormatter;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('csa_guzzle');
        // @phpstan-ignore-next-line
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config < 4.2
            // @phpstan-ignore-next-line
            $rootNode = $treeBuilder->root('csa_guzzle');
        }

        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('profiler')
                    ->canBeEnabled()
                    ->children()
                        ->integerNode('max_body_size')
                            ->info('The maximum size of the body which should be stored in the profiler (in bytes)')
                            ->example('65536')
                            ->defaultValue(GuzzleCollector::MAX_BODY_SIZE)
                        ->end()
                    // @phpstan-ignore-next-line
                    ->end()
                ->end()
                ->arrayNode('logger')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        // @phpstan-ignore-next-line
                        ->scalarNode('format')
                            ->beforeNormalization()
                                ->ifInArray(['clf', 'debug', 'short'])
                                ->then(function ($v) {
                                    return constant('GuzzleHttp\MessageFormatter::'.strtoupper($v));
                                })
                            ->end()
                            ->defaultValue(MessageFormatter::CLF)
                        ->end()
                        ->scalarNode('level')
                            ->beforeNormalization()
                                ->ifInArray([
                                    'emergency', 'alert', 'critical', 'error',
                                    'warning', 'notice', 'info', 'debug',
                                ])
                                ->then(function ($v) {
                                    return constant('Psr\Log\LogLevel::'.strtoupper($v));
                                })
                            ->end()
                            ->defaultValue('debug')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_client')->info('The first client defined is used if not set')->end()
                ->booleanNode('autoconfigure')->defaultFalse()->end()
                ->append($this->createCacheNode())
                ->append($this->createClientsNode())
                ->append($this->createMockNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function createCacheNode()
    {
        $treeBuilder = new TreeBuilder('cache');
        // @phpstan-ignore-next-line
        if (method_exists($treeBuilder, 'getRootNode')) {
            $node = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config < 4.2
            // @phpstan-ignore-next-line
            $node = $treeBuilder->root('cache');
        }

        $node
            ->canBeEnabled()
            ->validate()
                ->ifTrue(function ($v) : bool {
                    return $v['enabled'] && null === $v['adapter'];
                })
                ->thenInvalid('The \'csa_guzzle.cache.adapter\' key is mandatory if you enable the cache middleware')
            ->end()
            ->children()
                ->scalarNode('adapter')->defaultNull()->end()
            // @phpstan-ignore-next-line
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function createClientsNode()
    {
        $treeBuilder = new TreeBuilder('clients');
        // @phpstan-ignore-next-line
        if (method_exists($treeBuilder, 'getRootNode')) {
            $node = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config < 4.2
            // @phpstan-ignore-next-line
            $node = $treeBuilder->root('clients');
        }

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('class')->defaultValue('GuzzleHttp\Client')->end()
                    ->booleanNode('lazy')->defaultFalse()->end()
                    ->variableNode('config')->end()
                    ->arrayNode('middleware')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('alias')->defaultNull()->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function createMockNode()
    {
        $treeBuilder = new TreeBuilder('mock');
        // @phpstan-ignore-next-line
        if (method_exists($treeBuilder, 'getRootNode')) {
            $node = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config < 4.2
            // @phpstan-ignore-next-line
            $node = $treeBuilder->root('mock');
        }

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('storage_path')->isRequired()->end()
                // @phpstan-ignore-next-line
                ->scalarNode('mode')->defaultValue('replay')->end()
                // @phpstan-ignore-next-line
                ->arrayNode('request_headers_blacklist')
                    ->prototype('scalar')->end()
                // @phpstan-ignore-next-line
                ->end()
                ->arrayNode('response_headers_blacklist')
                    ->prototype('scalar')->end()
                // @phpstan-ignore-next-line
                ->end()
            ->end()
        ;

        return $node;
    }
}
