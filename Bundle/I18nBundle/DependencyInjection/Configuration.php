<?php

namespace Victoire\Bundle\I18nBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;

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
        $rootNode = $treeBuilder->root('victoire_i18n');

        $rootNode
            ->children()
                ->scalarNode('victoire_locale')->defaultValue('fr')->end()
            ->end()
            ->children()
                ->scalarNode('locale_pattern')->defaultValue(LocaleResolver::PATTERN_PARAMETER)->end()
            ->end()
            ->children()
                ->arrayNode('locale_pattern_table')
                    ->useAttributeAsKey(true)
                    ->normalizeKeys(false)
                    ->prototype('scalar')
                    ->end()
                ->defaultValue([])
                ->end()
            ->end()
            ->children()
                ->arrayNode('users_locale_domains')
                    ->useAttributeAsKey(true)
                    ->prototype('scalar')
                    ->end()
                ->defaultValue(['victoire'])
                ->end()
            ->end()
            ->children()
                ->arrayNode('available_locales')
                    ->useAttributeAsKey(true)
                    ->prototype('scalar')
                    ->end()
                ->defaultValue(['%locale%'])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
