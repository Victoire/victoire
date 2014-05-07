<?php

namespace Victoire\Bundle\FormBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VictoireFormExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $xmlLoader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $xmlLoader->load('twig.xml');

        /**
         * Form
         */

        // if (isset($config['form'])) {
            $xmlLoader->load('form.xml');
            foreach ($config['form'] as $key => $value) {
                if (is_array($value)) {
                    $this->remapParameters($container, 'victoire_form.form.'.$key, $config['form'][$key]);
                } else {
                    $container->setParameter(
                        'victoire_form.form.'.$key,
                        $value
                    );
                }
            }
        // }

        /**
         * Icons
         */
        if (isset($config['icons'])) {
            $this->remapParameters($container, 'victoire_form.icons', $config['icons']);
        }
    }

    /**
     * Remap parameters.
     *
     * @param ContainerBuilder $container
     * @param string           $prefix
     * @param array            $config
     */
    private function remapParameters(ContainerBuilder $container, $prefix, array $config)
    {
        foreach ($config as $key => $value) {
            $container->setParameter(sprintf('%s.%s', $prefix, $key), $value);
        }
    }
}
