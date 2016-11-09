<?php

namespace Victoire\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VictoireCoreExtension extends Extension
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
        $loader->load('forms.yml');

        // We instanciate a new kernel and iterate on all it's bundles to load the victoire_core configs
        $kernel = new \AppKernel('prod', false);
        foreach ($kernel->registerBundles() as $bundle) {
            $path = $bundle->getPath();
            $yamlParser = new Yaml($container, $path.'/Resources/config/config.yml');
            $victoireConfig = $yamlParser->parse($path.'/Resources/config/config.yml');
            if (is_array($victoireConfig) && array_key_exists('victoire_core', $victoireConfig)) {
                $config['widgets'] = array_merge($config['widgets'], $victoireConfig['victoire_core']['widgets'] ?: []);
            }
        }

        $container->setParameter(
            'victoire_core.cache_dir', $config['cache_dir']
        );
        $container->setParameter(
            'victoire_core.business_entity_debug', $config['business_entity_debug']
        );
        if (array_key_exists('templates', $config)) {
            $container->setParameter(
                'victoire_core.templates', $config['templates']
            );
        } else {
            $container->setParameter(
                'victoire_core.templates', ''
            );
        }
        $container->setParameter(
            'victoire_core.widgets', $config['widgets']
        );
        $container->setParameter(
            'victoire_core.layouts', $config['layouts']
        );
        $container->setParameter(
            'victoire_core.modal_layouts', $config['modal_layouts']
        );
        $container->setParameter(
            'victoire_core.slots', $config['slots']
        );
        $container->setParameter(
            'victoire_core.user_class', $config['user_class']
        );
        $container->setParameter(
            'victoire_core.base_paths', $config['base_paths']
        );
        $container->setParameter(
            'victoire_core.base_paths', $config['base_paths']
        );
        $container->setParameter(
            'victoire_core.businessTemplates', $config['businessTemplates']
        );
    }
}
