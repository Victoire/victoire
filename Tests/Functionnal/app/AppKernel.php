<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),

            //Victoire bundles
            new Victoire\Bundle\CoreBundle\VictoireCoreBundle(),
            new Victoire\Bundle\TwigBundle\VictoireTwigBundle(),
            new Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
            new Victoire\Bundle\BusinessEntityBundle\VictoireBusinessEntityBundle(),
            new Victoire\Bundle\BusinessEntityPageBundle\VictoireBusinessEntityPageBundle(),
            new Victoire\Bundle\DashboardBundle\VictoireDashboardBundle(),
            new Victoire\Bundle\FilterBundle\VictoireFilterBundle(),
            new Victoire\Bundle\I18nBundle\VictoireI18nBundle(),
            new Victoire\Bundle\FormBundle\VictoireFormBundle(),
            new Victoire\Bundle\PageBundle\VictoirePageBundle(),
            new Victoire\Bundle\QueryBundle\VictoireQueryBundle(),
            new Victoire\Bundle\MediaBundle\VictoireMediaBundle(),
            new Victoire\Bundle\SeoBundle\VictoireSeoBundle(),
            new Victoire\Bundle\SitemapBundle\VictoireWidgetSitemapBundle(),
            new Victoire\Bundle\TemplateBundle\VictoireTemplateBundle(),
            new Victoire\Bundle\UserBundle\VictoireUserBundle(),
            new Victoire\Bundle\WidgetBundle\VictoireWidgetBundle(),
            new Victoire\Bundle\WidgetMapBundle\VictoireWidgetMapBundle(),
            //Victoire test widget
            new Victoire\Widget\AnakinBundle\VictoireWidgetAnakinBundle(),
            //AppVentus Bundles
            new AppVentus\AsseticInjectorBundle\AvAsseticInjectorBundle(),
        );
    }

    /**
     * @return null
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/Victoire/cache';
    }
    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/Victoire/logs';
    }
}
