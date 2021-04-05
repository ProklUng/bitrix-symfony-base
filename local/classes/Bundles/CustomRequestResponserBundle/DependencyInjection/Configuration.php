<?php

namespace Local\Bundles\CustomRequestResponserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\CustomArgumentResolverBundle\DependencyInjection
 *
 * @since 04.12.2020
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('custom_request_responser');
        $rootNode    = $treeBuilder->getRootNode();

        \assert($rootNode instanceof ArrayNodeDefinition);

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
                ->arrayNode('middlewares_disabled')
                ->useAttributeAsKey('name')
                ->prototype('boolean')->defaultValue(true)->end()
            ->end()
            ->end()

            ->children()
                ->arrayNode('bitrix_middlewares_disabled')
                ->useAttributeAsKey('name')
                ->prototype('boolean')->defaultValue(true)->end()
            ->end()
            ->end()

            ->children()
            ->arrayNode('headers')
            ->arrayPrototype()
            ->beforeNormalization()
            ->ifTrue(static function ($v): bool {
                return is_string($v) && strpos($v, ':') !== false;
            })
            ->then(static function (string $v): array {
                [$name, $value] = explode(':', $v, 2);
                return ['name' => trim($name), 'value' => trim($value)];
            })
            ->end()
            ->beforeNormalization()
            ->ifTrue(static function ($v): bool {
                return is_array($v)
                    && count($v) === 1
                    && is_string(key($v))
                    && is_string(reset($v));
            })
            ->then(static function (array $v): array {
                return ['name' => key($v), 'value' => reset($v)];
            })
            ->end()
            ->normalizeKeys(false)
            ->children()
            ->scalarNode('name')->end()
            ->scalarNode('value')->end()
            ->scalarNode('condition')
            ->defaultNull()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
