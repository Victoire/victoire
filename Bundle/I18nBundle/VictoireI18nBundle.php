<?php

namespace Victoire\Bundle\I18nBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Victoire\Bundle\I18nBundle\DependencyInjection\Compiler\I18nCompilerPass;

class VictoireI18nBundle extends Bundle
{
	 /**
     * the function add a cache driver to get and track i18n entity
     */
    public function boot()
    {
        $driverChain = $this->container->get('doctrine.orm.entity_manager')->getConfiguration()->getMetadataDriverImpl();
        $proxyDriver = $this->container->get('victoire_i18n.i18n.cache_driver');
        $driverChain->addDriver($proxyDriver, 'Victoire\\Bundle\\I18nBundle');
    }

    /**
     * Build bundle
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new I18nCompilerPass());
    }
    
}
