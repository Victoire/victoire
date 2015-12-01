<?php

namespace Victoire\Bundle\ViewReferenceBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ViewReferenceTransformerCompilerPass.
 */
class ViewReferenceTransformerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('victoire_view_reference.transformer_chain')) {
            return;
        }
        $definition = $container->getDefinition(
            'victoire_view_reference.transformer_chain'
        );
        $taggedServices = $container->findTaggedServiceIds(
            'victoire_view_reference.transformer'
        );
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!array_key_exists('viewNamespace', $attributes)) {
                    throw new InvalidConfigurationException('ViewNamespace class attribute is not defined for service '.$id);
                }
                if (!array_key_exists('outputFormat', $attributes)) {
                    throw new InvalidConfigurationException('OutputFormat (xml|array) attribute is not defined for service '.$id);
                }
                $definition->addMethodCall(
                    'addTransformer',
                    [new Reference($id), $attributes['viewNamespace'], $attributes['outputFormat']]
                );
            }
        }
    }
}
