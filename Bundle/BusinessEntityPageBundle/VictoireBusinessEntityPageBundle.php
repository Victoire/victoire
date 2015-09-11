<?php

namespace Victoire\Bundle\BusinessEntityPageBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\BusinessEntityPageBundle\DependencyInjection\Compiler\BusinessTemplateCompilerPass;

/**
 * The Business Entity Page Pattern bundle
 */
class VictoireBusinessEntityPageBundle extends Bundle
{
    /**
     * Build bundle
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BusinessTemplateCompilerPass());
    }
}
