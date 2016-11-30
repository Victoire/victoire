<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

require_once __DIR__.'/autoload.php';

class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),
            new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new Troopers\AsseticInjectorBundle\TroopersAsseticInjectorBundle(),
            new Troopers\AlertifyBundle\TroopersAlertifyBundle(),

            //Victoire bundles
            new Victoire\Bundle\AnalyticsBundle\VictoireAnalyticsBundle(),
            new Victoire\Bundle\CoreBundle\VictoireCoreBundle(),
            new Victoire\Bundle\CriteriaBundle\VictoireCriteriaBundle(),
            new Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
            new Victoire\Bundle\BusinessEntityBundle\VictoireBusinessEntityBundle(),
            new Victoire\Bundle\BusinessPageBundle\VictoireBusinessPageBundle(),
            new Victoire\Bundle\FilterBundle\VictoireFilterBundle(),
            new Victoire\Bundle\I18nBundle\VictoireI18nBundle(),
            new Victoire\Bundle\FormBundle\VictoireFormBundle(),
            new Victoire\Bundle\PageBundle\VictoirePageBundle(),
            new Victoire\Bundle\QueryBundle\VictoireQueryBundle(),
            new Victoire\Bundle\MediaBundle\VictoireMediaBundle(),
            new Victoire\Bundle\SeoBundle\VictoireSeoBundle(),
            new Victoire\Bundle\SitemapBundle\VictoireSitemapBundle(),
            new Victoire\Bundle\TemplateBundle\VictoireTemplateBundle(),
            new Victoire\Bundle\TwigBundle\VictoireTwigBundle(),
            new Victoire\Bundle\UIBundle\VictoireUIBundle(),
            new Victoire\Bundle\UserBundle\VictoireUserBundle(),
            new Victoire\Bundle\ViewReferenceBundle\ViewReferenceBundle(),
            new Victoire\Bundle\WidgetBundle\VictoireWidgetBundle(),
            new Victoire\Bundle\WidgetMapBundle\VictoireWidgetMapBundle(),
            new Victoire\Bundle\ORMBusinessEntityBundle\VictoireORMBusinessEntityBundle(),
            //Victoire test bundles
            new Victoire\Widget\ForceBundle\VictoireWidgetForceBundle(),
            new Victoire\Widget\LightSaberBundle\VictoireWidgetLightSaberBundle(),
            new Victoire\Widget\ButtonBundle\VictoireWidgetButtonBundle(),
            new Victoire\Widget\TextBundle\VictoireWidgetTextBundle(),
            new Acme\AppBundle\AcmeAppBundle(),
        ];
    }

    /**
     * @param LoaderInterface $loader
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
        return sys_get_temp_dir().'/Victoire/cache/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/Victoire/logs/'.$this->environment;
    }
}
