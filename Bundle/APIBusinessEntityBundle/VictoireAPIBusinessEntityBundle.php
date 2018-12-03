<?php

namespace Victoire\Bundle\APIBusinessEntityBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\APIBusinessEntityBundle\DependencyInjection\Compiler\APIAuthenticationCompilerPass;

/**
 * {@inheritdoc}
 */
class VictoireAPIBusinessEntityBundle extends Bundle
{
    /**
     * Build bundle.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new APIAuthenticationCompilerPass());
    }
}
