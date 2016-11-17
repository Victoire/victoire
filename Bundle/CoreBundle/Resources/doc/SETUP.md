#How to install Victoire DCMS ?

You actually have two ways to install it :

## 1. From scratch using Victoire Demo

Follow the instructions on [victoire demo Readme](https://github.com/Victoire/demo/blob/master/README.md)

## 2. In an existing Symfony project

- Install Victoire core :

```bash
composer require victoire/victoire
```

- Declare in AppKernel:

```php
    //dependencies
    new Troopers\AsseticInjectorBundle\TroopersAsseticInjectorBundle(),
    new Troopers\AlertifyBundle\TroopersAlertifyBundle(),
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
    new Victoire\Bundle\CriteriaBundle\VictoireCriteriaBundle(),
    new Victoire\Bundle\FilterBundle\VictoireFilterBundle(),
    new Victoire\Bundle\FormBundle\VictoireFormBundle(),
    new Victoire\Bundle\I18nBundle\VictoireI18nBundle(),
    new Victoire\Bundle\MediaBundle\VictoireMediaBundle(),
    new Victoire\Bundle\PageBundle\VictoirePageBundle(),
    new Victoire\Bundle\QueryBundle\VictoireQueryBundle(),
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

- Create `victoire.yml` config file in `app/config`:

```yml
victoire_core:
    user_class: Victoire\Bundle\UserBundle\Entity\User
    templates:
        layout: "::layout.html.twig"
    layouts:
        fullWidth: "Full width content"
    slots:
        header_logo:
            max: 1
            widgets:
                image: ~
```

- Add the wanted widgets:

```json
    "friendsofvictoire/text-widget"      : "~2.0",
    "friendsofvictoire/button-widget"    : "~2.0",
    "friendsofvictoire/image-widget"     : "~2.0",
    "friendsofvictoire/render-widget"    : "~2.0",
    "friendsofvictoire/breadcrumb-widget": "~2.0",
    ...
```

Get the whole Victoire Widget list [**here**](http://packagist.org/search/?tags=victoire)

Check Victoire dependencies:

```json
    "knplabs/knp-menu": "~2.1",
    "knplabs/knp-menu-bundle": "~2.1",
    "friendsofsymfony/user-bundle": "~2.0",
    "stof/doctrine-extensions-bundle": "~1.2",
    "troopers/alertify-bundle": "^1.1",
    "troopers/assetic-injector-bundle": "^1.0",
    "knplabs/gaufrette": "v0.1.7",
    "knplabs/knp-gaufrette-bundle": "v0.1.7",
```

- Setup routing:

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

- Enable StofDoctrineExtensions in `AppKernel`:

```php
    //dependencies
    [...]
    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
```

- Add this config in `doctrine.yml`:

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

- Add this config in stof_doctrine_extensions (imported in `config.yml`)

```yml
stof_doctrine_extensions:
    default_locale: fr_FR
    orm:
        default:
            tree: true
```

- Install FOSJSRouting:

```bash
composer require friendsofsymfony/jsrouting-bundle
```

- Enable the `localizeddate` filter:

```yml
twig.extension.intl:
    class: Twig_Extensions_Extension_Intl
    tags:
        - { name: twig.extension }
```

- Import the Victoire config file:

```yml
imports:
    - { resource: @VictoireCoreBundle/Resources/config/config.yml }
```

- Install Redis:

```bash
wget http://download.redis.io/redis-stable.tar.gz
tar xvzf redis-stable.tar.gz
cd redis-stable
make
```

- Setup Redis:

```yml
snc_redis:
   clients:
       victoire:
           type: predis
           alias: victoire
           dsn: redis://localhost
```

- Start Redis server:

```bash
redis-server
```

- Set a CRON:

```
* * * * * bin/console victoire:widget-css:generate --limit 20 --outofdate
```

This command allow you to regenerate View's CSS

- Log in:

Use the following information to login and start to create your website:

|Login|Password|
|-----|--------|
|`anakin@victoire.io`|test|
