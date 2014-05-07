<?php

namespace Victoire\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('victoire_cms');

        $rootNode
            ->children()
                ->scalarNode('user_class')->defaultNull()->end()
            ->end()
            ->children()
                ->scalarNode('cache_dir')->defaultValue('%kernel.cache_dir%/victoire/Entity')->end()
            ->end()
            ->children()
                ->variableNode('applicative_bundle')
                ->defaultNull()
                ->end()
            ->end()
            ->children()
                ->variableNode('available_frameworks')
                ->defaultValue(array('bootstrap', 'foundation'))
                ->end()
            ->end()
            ->children()
                ->variableNode('framework')
                ->defaultValue('bootstrap')
                ->end()
            ->end()
            ->children()
                ->variableNode('templates')
                ->defaultValue(array('layout' => 'VictoireCoreBundle::layout.html.twig'))
                ->end()
            ->end()
            ->children()
                ->variableNode('layouts')
                ->defaultValue(array())
                ->end()
            ->end()
            ->children()
                ->arrayNode('widgets')
                    ->useAttributeAsKey(true)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('name')->end()
                        ->end()
                    ->end()
                ->defaultValue(array())
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
