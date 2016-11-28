<?php

namespace Victoire\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('victoire_core');

        $rootNode
            ->children()
                ->scalarNode('user_class')
                    ->defaultNull()
                ->end()
            ->end()
            ->children()
                ->arrayNode('domain_name')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ->children()
                ->scalarNode('cache_dir')
                    ->defaultValue('%kernel.cache_dir%/victoire')
                ->end()
            ->end()
            ->children()
                ->scalarNode('business_entity_debug')
                    ->defaultValue('%kernel.debug%')
                ->end()
            ->end()
            ->children()
                ->variableNode('base_paths')
                    ->defaultValue(['%kernel.root_dir%/../src', '%kernel.root_dir%/../vendor/victoire/victoire/Bundle/BlogBundle', '%kernel.root_dir%/../vendor/friendsofvictoire'])
                ->end()
            ->end()
            ->children()
                ->variableNode('businessTemplates')
                    ->defaultNull()
                    ->treatNullLike([])
                ->end()
            ->end()
            ->children()
                ->variableNode('templates')
                    ->defaultValue(['layout' => 'VictoireCoreBundle:Layout:layout.html.twig'])
                ->end()
            ->end()
            ->children()
                ->variableNode('layouts')
                    ->defaultValue([])
                ->end()
            ->end()
            ->children()
                ->variableNode('modal_layouts')
                    ->defaultValue(['modal'])
                ->end()
            ->end()
            ->children()
                ->arrayNode('widgets')
                    ->useAttributeAsKey(true)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('name')->end()
                            ->scalarNode('cache')->defaultTrue()->end()
                            ->scalarNode('cache_timeout')->defaultValue(7 * 24 * 60 * 1000)->end() //one week
                        ->end()
                    ->end()
                ->defaultValue([])
                ->end()
            ->end()
            ->children()
                ->arrayNode('slots')
                    ->useAttributeAsKey(true)
                    ->prototype('array')
                    ->children()
                        ->integerNode('position')->end()
                        ->integerNode('max')->end()
                        ->variableNode('widgets')->end()
                        ->scalarNode('class')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
