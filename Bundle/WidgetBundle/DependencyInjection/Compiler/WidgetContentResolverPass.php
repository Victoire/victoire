<?php

namespace Victoire\Bundle\WidgetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WidgetContentResolverPass implements CompilerPassInterface
{
    /**
     * Process filter.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('victoire_widget.widget_content_resolver_chain')) {
            $definition = $container->getDefinition(
                'victoire_widget.widget_content_resolver_chain'
            );

            $taggedServices = $container->findTaggedServiceIds(
                'victoire_widget.widget_content_resolver'
            );

            foreach ($taggedServices as $id => $attributes) {
                foreach ($attributes as $attribute) {
                    $definition->addMethodCall(
                        'addResolver',
                        [$attribute['alias'], new Reference($id)]
                    );
                }
            }
        }
    }
}
