<?php

namespace Victoire\Bundle\BusinessEntityBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\BusinessEntityBundle\DependencyInjection\Compiler\BusinessEntityResolverCompilerPass;

/**
 * The Victoire Business Entity Bundle.
 */
class VictoireBusinessEntityBundle extends Bundle
{

    /**
     * Build bundle.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BusinessEntityResolverCompilerPass());
    }
}
