<?php

namespace Victoire\Bundle\ThemeBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\ThemeBundle\DependencyInjection\Compiler\ThemePass;

/**
 *
 * @author Paul Andrieux
 *
 */
class VictoireThemeBundle extends Bundle
{

    /**
     * Build bundle
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ThemePass());
    }
}
