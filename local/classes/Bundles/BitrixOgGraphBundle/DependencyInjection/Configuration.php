<?php

namespace Local\Bundles\BitrixOgGraphBundle\DependencyInjection;

use CSite;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Local\Bundles\BitrixToolsBundle\DependencyInjection
 *
 * @since 07.12.2020
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('bitrixoggraph');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            // @phpstan-ignore-next-line
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->scalarNode('site_name')->defaultValue($this->getDefaultSiteName())->end()
                ->scalarNode('img')->defaultValue('')->end()
                ->scalarNode('fb_admins')->defaultValue('')->end()
                ->scalarNode('article_publisher')->defaultValue('')->end()

            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Название сайта.
     *
     * @return string
     */
    private function getDefaultSiteName() : string
    {
        $rsSites = CSite::GetByID(SITE_ID);
        $arSite = $rsSites->Fetch();

        return (string)$arSite['SITE_NAME'];
    }
}
