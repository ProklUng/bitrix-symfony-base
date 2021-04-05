<?php

namespace Local\Bundles\SymfonyMailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\SymfonyMailerBundle\DependencyInjection
 *
 * @since 02.03.2021
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfony_mailer');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultValue(true)->end()
                ->scalarNode('default_email_from_adress')->defaultValue('')->end()
                ->scalarNode('default_email_from_title')->defaultValue('')->end()
                ->scalarNode('admin_email')->defaultValue('')->end()
                ->scalarNode('dsn')->defaultValue('')->end()
                ->scalarNode('dsn_file')->defaultValue('')->end()
                ->booleanNode('mock_sending_email')->defaultValue(false)->end()
                ->arrayNode('envelope')
                    ->info('Mailer Envelope configuration')
                    ->children()
                    ->scalarNode('sender')->end()
                    ->arrayNode('recipients')
                    ->performNoDeepMerging()
                    ->beforeNormalization()
                    ->ifArray()
                    ->then(function ($v) {
                        return array_filter(array_values($v));
                    })
                    ->end()
                    ->prototype('scalar')->end()
                    ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
