#Setup

## You can start a project from the victoire/demo package

    composer create-project victoire/demo myVictoire 1.0.*@dev

Import the database in var/dump/db.sql

or do it yourself by doing the usual stuff (doctrine:schema:update etc). You can also load fixtures with the following lines :

    php bin/console doctrine:database:create
    php bin/console doctrine:schema:create
    php bin/console victoire:generate:view-cache --env=dev
    php bin/console doctrine:fixtures:load --fixtures="vendor/victoire/victoire/Bundle/CoreBundle/DataFixtures/ORM"

*Careful* : please notice that Victoire needs APC in CLI mode. to do so, add these two lines in your php.ini config file

```ini
    apc.enabled = 1
    apc.enable_cli = 1
```

## Manually

- Install Victoire core  :

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
    new JMS\DiExtraBundle\JMSDiExtraBundle($this),
    new Liip\ImagineBundle\LiipImagineBundle(),
    new Knp\Bundle\MenuBundle\KnpMenuBundle(),
    //Victoire bundles
    new Victoire\Bundle\AnalyticsBundle\VictoireAnalyticsBundle(),
    new Victoire\Bundle\AnalyticsBundle\VictoireAnalyticsBundle(),
    new Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
    new Victoire\Bundle\BusinessEntityBundle\VictoireBusinessEntityBundle(),
    new Victoire\Bundle\BusinessEntityPageBundle\VictoireBusinessEntityPageBundle(),
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
    new Victoire\Bundle\WidgetBundle\VictoireWidgetBundle(),
    new Victoire\Bundle\WidgetMapBundle\VictoireWidgetMapBundle(),
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

Get the whole Victoire Widget list at packagist.org/search/?tags=victoire


Check victoire dependencies:

```json
    "knplabs/knp-menu"                       : "2.0.0-alpha1",
    "knplabs/knp-menu-bundle"                : "2.0.0-alpha1",
    "friendsofsymfony/user-bundle"           : "dev-master",
    "stof/doctrine-extensions-bundle"        : "1.2.*@dev",
    "appventus/assetic-injector-bundle"      : "dev-master",
    "appventus/alertify-bundle"              : "dev-master",
    "appventus/shortcuts-bundle"             : "dev-master",
    "knplabs/gaufrette"                      : "v0.1.7",
    "knplabs/knp-gaufrette-bundle"           : "v0.1.7",
    "knplabs/knp-components"                 : "dev-master",
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

- enable the localizeddate filter:
```yml
        twig.extension.intl:
            class: Twig_Extensions_Extension_Intl
            tags:
                - { name: twig.extension }
```
- import the victoire config file:

```yml
imports:
        - { resource: @VictoireCoreBundle/Resources/config/config.yml }
```
Use the following information to login and start to create your website

|Login|Password|
|-----|--------|
|`anakin@victoire.io`|test|


