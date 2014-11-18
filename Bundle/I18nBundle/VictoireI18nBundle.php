<?php

namespace Victoire\Bundle\I18nBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Victoire\Bundle\I18nBundle\DependencyInjection\Compiler\I18nCompilerPass;

class VictoireI18nBundle extends Bundle
{
	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new I18nCompilerPass());
    }
}
