<?php
namespace Victoire\Bundle\WidgetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WidgetContentResolverPass implements CompilerPassInterface
{
    /**
     * Process filter
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('victoire_widget.widget_content_resolver_chain')) {

            $definition = $container->getDefinition(
                'victoire_widget.widget_content_resolver_chain'
            );
            $definition->setScope('request');

            $taggedServices = $container->findTaggedServiceIds(
                'victoire_widget.widget_content_resolver'
            );

            foreach ($taggedServices as $id => $attributes) {
                if (empty($attributes[0]['alias'])) {
                    throw new \Exception("The content resolver " . $id . "has no alias in its tags. Please define the alias with the widget name.");
                }

                $definition->addMethodCall(
                    'addResolver',
                    array($attributes[0]['alias'], new Reference($id))
                );

                $resolverDefinition = $container->getDefinition($id);
                $resolverDefinition->addMethodCall(
                    'setQueryHelper',
                    array(new Reference('victoire_query.query_helper'))
                );
                $resolverDefinition->addMethodCall(
                    'setFilterChain',
                    array(new Reference('victoire_core.filter_chain'))
                );
                $resolverDefinition->addMethodCall(
                    'setRequest',
                    array(new Reference('request'))
                );
                $resolverDefinition->setScope('request');
            }
        }
    }
}
