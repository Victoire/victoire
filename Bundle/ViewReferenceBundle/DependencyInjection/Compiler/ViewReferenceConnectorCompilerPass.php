<?php

namespace Victoire\Bundle\ViewReferenceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ViewReferenceConnectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $viewReferenceManagers= $container->findTaggedServiceIds(
            'view_reference.manager'
        );

        $viewReferenceRepositories= $container->findTaggedServiceIds(
            'view_reference.repository'
        );
        $connectors = [];
        $connectorType = $container->getParameter('victoire_view_reference.connector.type');
        foreach ($viewReferenceManagers as $id => $tags) {
            foreach ($tags as $attributes) {
                $connectors[$attributes["connector"]]['manager'] = $id;
            }
        }
        foreach ($viewReferenceRepositories as $id => $tags) {
            foreach ($tags as $attributes) {
                $connectors[$attributes["connector"]]['repository'] = $id;
            }
        }
        if(!isset($connectorType))
        {
            throw new \Exception('No manager and repository found to have '.$connectorType.' connector.');
        }
        if(!isset($connectors[$connectorType]['manager']))
        {
            throw new \Exception('No manager found to have '.$connectorType.' connector.');
        }
        if(!isset($connectors[$connectorType]['repository']))
        {
            throw new \Exception('No repository found to have '.$connectorType.' connector.');
        }
        $container->setAlias("victoire_view_reference.connector.manager", $connectors[$connectorType]['manager']);
        $container->setAlias("victoire_view_reference.connector.repository", $connectors[$connectorType]['repository']);
    }
}