<?php

namespace Victoire\Bundle\CriteriaBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DataSourceCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {

        if (!$container->hasDefinition('victoire_criteria.chain.data_source_chain')) {
            return;
        }
        $chainDefinition = $container->getDefinition(
            'victoire_criteria.chain.data_source_chain'
        );
        $taggedServices = $container->findTaggedServiceIds(
            'victoire_criteria'
        );
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $chainDefinition->addMethodCall(
                    'addDataSource',
                    [new Reference($id), $attributes]
                );
            }
        }
    }
}
