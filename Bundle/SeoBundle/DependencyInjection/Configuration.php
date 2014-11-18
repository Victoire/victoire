<?php

namespace Victoire\Bundle\SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 *
 * @author Paul Andrieux
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Get the config tree builder
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('victoire_seo');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('analytics')
                    ->prototype('array')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
