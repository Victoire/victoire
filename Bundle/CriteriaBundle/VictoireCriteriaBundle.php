<?php

namespace Victoire\Bundle\CriteriaBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\CriteriaBundle\DependencyInjection\Compiler\DataSourceCompilerPass;

class VictoireCriteriaBundle extends Bundle
{
    /**
     * Build bundle.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DataSourceCompilerPass());
    }
}
