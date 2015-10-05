<?php

namespace Victoire\Bundle\BusinessPageBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\BusinessPageBundle\DependencyInjection\Compiler\BusinessTemplateCompilerPass;

/**
 * The Business Entity Page Pattern bundle.
 */
class VictoireBusinessPageBundle extends Bundle
{
    /**
     * Build bundle.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BusinessTemplateCompilerPass());
    }
}
