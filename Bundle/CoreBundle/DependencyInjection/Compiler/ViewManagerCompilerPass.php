<?php

namespace Victoire\Bundle\CoreBundle\DependencyInjection\Compiler;


use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ViewManagerCompilerPass
 * @package Victoire\Bundle\CoreBundle\DependencyInjection\Compiler
 */
class ViewManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if(!$container->hasDefinition('victoire_core.chain.view_reference_builder_chain')){
            return;
        }
        $definition = $container->getDefinition(
            'victoire_core.chain.view_reference_builder_chain'
        );
        $taggedServices = $container->findTaggedServiceIds(
            'victoire_core.view_manager'
        );
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if( !array_key_exists('view', $attributes)){
                    throw new InvalidConfigurationException("View class attribute is not defined for " . $id);
                }
                $definition->addMethodCall(
                    'addViewReferenceBuilder',
                    array(new Reference($id), $attributes['view'])
                );
            }

        }
    }
}
