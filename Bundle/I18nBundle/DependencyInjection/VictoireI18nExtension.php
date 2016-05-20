<?php

namespace Victoire\Bundle\I18nBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VictoireI18nExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter(
            'victoire_i18n.available_locales', $config['available_locales']
        );
        $container->setParameter(
            'victoire_i18n.locale_pattern_table', $config['locale_pattern_table']
        );
        $container->setParameter(
            'victoire_i18n.victoire_locale', $config['victoire_locale']
        );
        $container->setParameter(
            'victoire_i18n.users_locale.domains', $config['users_locale_domains']
        );
        $container->setParameter(
            'victoire_i18n.locale_pattern', $config['locale_pattern']
        );
    }
}
