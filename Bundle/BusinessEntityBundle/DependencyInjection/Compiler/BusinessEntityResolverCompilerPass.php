<?php

namespace Victoire\Bundle\BusinessEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler is used to inject the security for routes /victoire-dcms
 * without needs to define it in the application security.yml.
 *
 * @author Paul Andrieux
 **/
class BusinessEntityResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('victoire_business_entity.resolver.business_entity_resolver')) {
            return;
        }
        $chainDefinition = $container->getDefinition(
            'victoire_business_entity.resolver.business_entity_resolver'
        );
        $taggedServices = $container->findTaggedServiceIds(
            'victoire_business_entity.resolver'
        );

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $chainDefinition->addMethodCall(
                    'addResolver',
                    [new Reference($id), $attributes['type']]
                );
            }
        }
    }
}
