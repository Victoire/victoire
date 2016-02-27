<?php

namespace Victoire\Bundle\FilterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 */
class FilterCompilerPass implements CompilerPassInterface
{
    /**
     * Process filter.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('victoire_core.filter_chain')) {
            $definition = $container->getDefinition(
                'victoire_core.filter_chain'
            );

            $taggedServices = $container->findTaggedServiceIds(
                'victoire_core.filter'
            );

            foreach ($taggedServices as $id => $attributes) {
                $definition->addMethodCall('addFilter', [
                        new Reference($id)
                    ]
                );
            }
        }
    }
}
