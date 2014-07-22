<?php

namespace Victoire\Bundle\DashboardBundle\DependencyInjection;

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
     * Get the tree builder
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('victoire_dashboard');

        return $treeBuilder;
    }
}
