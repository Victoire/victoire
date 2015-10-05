<?php

namespace Victoire\Bundle\BusinessPageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class BusinessTemplateCompilerPass.
 */
class BusinessTemplateCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('victoire_business_page.BusinessTemplate_chain')) {
            return;
        }
        $chainDefinition = $container->getDefinition(
            'victoire_business_page.BusinessTemplate_chain'
        );
        $taggedServices = $container->findTaggedServiceIds(
            'victoire_core.bussinessEntityPagePattern'
        );

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $chainDefinition->addMethodCall(
                    'addBusinessTemplate',
                    [new Reference($id), $attributes['alias']]
                );
            }
        }
    }
}
