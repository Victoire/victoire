<?php

namespace Victoire\Bundle\I18nBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Victoire\Bundle\I18nBundle\DependencyInjection\Compiler\I18nCompilerPass;

class VictoireI18nBundle extends Bundle
{
	 /**
     * create admin menu, add listeners for generate contextual menu item and dispatch globals item menus
     */
    public function boot()
    {

        $driverChain = $this->container->get('doctrine.orm.entity_manager')->getConfiguration()->getMetadataDriverImpl();

        $proxyDriver = $this->container->get('victoire_i18n.i18n.cache_driver');
        $driverChain->addDriver($proxyDriver, 'VictoireI18n');

    }

	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new I18nCompilerPass());
    }
}
