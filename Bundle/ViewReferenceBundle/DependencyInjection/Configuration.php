<?php

namespace Victoire\Bundle\ViewReferenceBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('victoire_view_reference');

        $rootNode
            ->children()
                ->scalarNode('cache_path')->defaultValue('%kernel.cache_dir%/victoire/viewsReferences.xml')->end()
                ->scalarNode('connector_type')->defaultValue('redis')->end()
            ->end();

        return $treeBuilder;
    }
}
