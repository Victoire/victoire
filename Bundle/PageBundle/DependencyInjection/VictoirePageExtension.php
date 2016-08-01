<?php

namespace Victoire\Bundle\PageBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VictoirePageExtension extends Extension implements PrependExtensionInterface
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
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        // Build fos_elastica config for each widget
        $elasticaConfig = [
            'indexes' => [
                'pages' => [
                    'types' => [
                         'Pages' => [
                            'serializer'  => [
                                'groups' => ['search'],
                            ],
                            'mappings'    => [
                                'translations' => [
                                    'type'       => 'nested',
                                    'properties' => [
                                        'name' => null,
                                    ],
                                ],
                            ],
                            'persistence' => [
                                'driver'   => 'orm',
                                'model'    => 'Victoire\\Bundle\\PageBundle\\Entity\\BasePage',
                                'provider' => [],
                                'listener' => [],
                                'finder'   => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case 'fos_elastica':
                    $container->prependExtensionConfig($name, $elasticaConfig);
                    break;
            }
        }
    }
}
