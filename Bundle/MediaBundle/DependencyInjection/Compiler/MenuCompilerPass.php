<?php

namespace Victoire\Bundle\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass makes it possible to adapt the menu
 */
class MenuCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('victoire_media.menubuilder')) {
            return;
        }

        $definition = $container->getDefinition('victoire_media.menubuilder');

        foreach ($container->findTaggedServiceIds('victoire_media.menu.adaptor') as $id => $attributes) {
            $definition->addMethodCall('addAdaptMenu', array(new Reference($id)));
        }
    }
}
