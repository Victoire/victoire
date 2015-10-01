<?php

namespace Victoire\Bundle\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * MediaHandlerCompilerPass.
 */
class MediaHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('victoire_media.media_manager')) {
            return;
        }

        $definition = $container->getDefinition('victoire_media.media_manager');

        foreach ($container->findTaggedServiceIds('victoire_media.media_handler') as $id => $attributes) {
            $definition->addMethodCall('addHandler', [new Reference($id)]);
        }
    }
}
