<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class APIAuthenticationCompilerPass.
 */
class APIAuthenticationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chainDefinition = $container->getDefinition(
            'victoire_api_business_entity.chain.api_authentication_chain'
        );
        $taggedServices = $container->findTaggedServiceIds(
            'victoire_api_business_entity.api_authentication'
        );

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $chainDefinition->addMethodCall(
                    'addAuthenticationMethod',
                    [new Reference($id)]
                );
            }
        }
    }
}
