#How to install Victoire DCMS ?

You actually have two ways to install it :

## 1. From scratch using Victoire Demo

Follow the instructions on [victoire demo Readme](https://github.com/Victoire/demo/blob/master/README.md)

## 2. In an existing Symfony project

- Install Victoire core :

    composer require victoire/victoire

- Declare in AppKernel:

```php
    //dependencies
    new AppVentus\AsseticInjectorBundle\AvAsseticInjectorBundle(),
    new AppVentus\AlertifyBundle\AvAlertifyBundle(),
    new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
    new FOS\UserBundle\FOSUserBundle(),
    new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
    new JMS\AopBundle\JMSAopBundle(),
    new JMS\TranslationBundle\JMSTranslationBundle(),
    new JMS\SerializerBundle\JMSSerializerBundle(),
    new JMS\DiExtraBundle\JMSDiExtraBundle($this),
    new Liip\ImagineBundle\LiipImagineBundle(),
    new Knp\Bundle\MenuBundle\KnpMenuBundle(),
    new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),
    new Snc\RedisBundle\SncRedisBundle(),
    //Victoire bundles
    new Victoire\Bundle\AnalyticsBundle\VictoireAnalyticsBundle(),
    new Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
    new Victoire\Bundle\BusinessEntityBundle\VictoireBusinessEntityBundle(),
    new Victoire\Bundle\BusinessPageBundle\VictoireBusinessPageBundle(),
    new Victoire\Bundle\CoreBundle\VictoireCoreBundle(),
    new Victoire\Bundle\FilterBundle\VictoireFilterBundle(),
    new Victoire\Bundle\FormBundle\VictoireFormBundle(),
    new Victoire\Bundle\I18nBundle\VictoireI18nBundle(),
    new Victoire\Bundle\MediaBundle\VictoireMediaBundle(),
    new Victoire\Bundle\PageBundle\VictoirePageBundle(),
    new Victoire\Bundle\QueryBundle\VictoireQueryBundle(),
    new Victoire\Bundle\ResourcesBundle\VictoireResourcesBundle(),
    new Victoire\Bundle\SeoBundle\VictoireSeoBundle(),
    new Victoire\Bundle\SitemapBundle\VictoireSitemapBundle(),
    new Victoire\Bundle\TemplateBundle\VictoireTemplateBundle(),
    new Victoire\Bundle\TwigBundle\VictoireTwigBundle(),
    new Victoire\Bundle\UserBundle\VictoireUserBundle(),
    new Victoire\Bundle\ViewReferenceBundle\ViewReferenceBundle(),
    new Victoire\Bundle\WidgetBundle\VictoireWidgetBundle(),
    new Victoire\Bundle\WidgetMapBundle\VictoireWidgetMapBundle(),
    //Victoire test bundles
    new Victoire\Widget\ForceBundle\VictoireWidgetForceBundle(),
    new Victoire\Widget\LightSaberBundle\VictoireWidgetLightSaberBundle(),
    new Victoire\Widget\ButtonBundle\VictoireWidgetButtonBundle(),
    new Victoire\Widget\TextBundle\VictoireWidgetTextBundle(),
    new Acme\AppBundle\AcmeAppBundle(),
```

- create victoire.yml config file:

```yml
    victoire_core:
        user_class: Victoire\Bundle\UserBundle\Entity\User
        applicative_bundle: AppBundle #Optional
        templates:
            layout: "::layout.html.twig"
        layouts:
            fullWidth: "Contenu unique"
        slots:
            header_logo:
                max: 1
                widgets:
                    image: ~
```

- add the wanted widgets:

```json
        "friendsofvictoire/text-widget"      : "dev-master",
        "friendsofvictoire/button-widget"    : "dev-master",
        "friendsofvictoire/image-widget"     : "dev-master",
        "friendsofvictoire/render-widget"    : "dev-master",
        "friendsofvictoire/breadcrumb-widget": "dev-master",
        ...
```

Get the whole Victoire Widget list [**here**](http://packagist.org/search/?tags=victoire)


Check victoire dependencies:

```json
    "knplabs/knp-menu"                       : "2.1.*@dev",
    "knplabs/knp-menu-bundle"                : "2.1.*@dev",
    "friendsofsymfony/user-bundle"           : "2.0.*@dev",
    "stof/doctrine-extensions-bundle"        : "1.2.*@dev",
    "appventus/assetic-injector-bundle"      : "dev-master",
    "appventus/alertify-bundle"              : "dev-master",
    "appventus/shortcuts-bundle"             : "dev-master",
    "knplabs/gaufrette"                      : "v0.1.7",
    "knplabs/knp-gaufrette-bundle"           : "v0.1.7",
    "knplabs/knp-components"                 : "1.3.*@dev",
```

- setup routing:

```yml
        fos_user_security:
            resource: "@FOSUserBundle/Resources/config/routing/security.xml"

        fos_user_resetting:
            resource: "@FOSUserBundle/Resources/config/routing/resetting.xml"
            prefix: /resetting

        fos_js_routing:
            resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

        VictoireCoreBundle:
            resource: .
            type: victoire
```


- Enable StofDoctrineExtensions in AppKernel

- add this config in doctrine.yml:

```yml
        orm:
           [...]
            mappings:
                gedmo_tree:
                    type: annotation
                    prefix: Gedmo\Tree\Entity
                    dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Tree/Entity"
                    is_bundle: false
```

- add this config in stof_doctrine_extensions (imported in config.yml)

```yml
    stof_doctrine_extensions:
        default_locale: fr_FR
        orm:
            default:
                tree: true
```

- install FOSJSRouting
```
    composer require friendsofsymfony/jsrouting-bundle
```

- enable the localizeddate filter

```yml
        twig.extension.intl:
            class: Twig_Extensions_Extension_Intl
            tags:
                - { name: twig.extension }
```
- import the victoire config file

```yml
imports:
        - { resource: @VictoireCoreBundle/Resources/config/config.yml }
```
- install redis
```
wget http://download.redis.io/redis-stable.tar.gz
tar xvzf redis-stable.tar.gz
cd redis-stable
make
```

- setup redis

```yml
snc_redis:
   clients:
       victoire:
           type: predis
           alias: victoire
           dsn: redis://localhost
```
- start redis server
```
redis-server
```

Use the following information to login and start to create your website

|Login|Password|
|-----|--------|
|`anakin@victoire.io`|test|
