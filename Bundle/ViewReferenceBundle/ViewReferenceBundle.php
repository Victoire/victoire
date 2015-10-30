<?php

namespace Victoire\Bundle\ViewReferenceBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\ViewReferenceBundle\DependencyInjection\Compiler\ViewReferenceBuilderCompilerPass;
use Victoire\Bundle\ViewReferenceBundle\DependencyInjection\Compiler\ViewReferenceTransformerCompilerPass;

class ViewReferenceBundle extends Bundle
{

    /**
     * Build bundle.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ViewReferenceBuilderCompilerPass());
        $container->addCompilerPass(new ViewReferenceTransformerCompilerPass());
    }
}
