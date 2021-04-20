<?php

declare(strict_types=1);

namespace Local\Bundles\UrlSignedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\UrlSignedBundle\DependencyInjection
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('urlsigned');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $childNode = $rootNode->children();

        /** @var NodeBuilder $childNode */
        $childNode = $childNode
                ->scalarNode('signer')
                    ->defaultValue('sha256')
                    ->cannotBeEmpty()
                    ->info('signer to use to create the signature')
                ->end()
        ;
        /** @var NodeBuilder $childNode */
        $childNode = $childNode
            ->scalarNode('signature_key')
                ->defaultValue('test key')
                // ->cannotBeEmpty()
                // ->isRequired()
                ->info('key used to sign the URL')
            ->end()
        ;
        /** @var NodeBuilder $childNode */
        $childNode = $childNode
            ->scalarNode('default_expiration')
                ->defaultValue(1)
                ->cannotBeEmpty()
                ->info('default expiration time in days')
            ->end()
        ;
        /** @var NodeBuilder $childNode */
        $childNode = $childNode
            ->scalarNode('expires_parameter')
                ->defaultValue('expires')
                ->cannotBeEmpty()
                ->info('name of the expires parameter in the URL')
            ->end()
        ;
        /** @var NodeBuilder $childNode */
        $childNode = $childNode
            ->scalarNode('signature_parameter')
                ->defaultValue('signature')
                ->cannotBeEmpty()
                ->info('name of the signature parameter in the URL')
            ->end()
        ;

        $childNode->end();

        return $treeBuilder;
    }
}
