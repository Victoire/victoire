#Setup

## You can start a project from the victoire/demo package

    composer create-project victoire/demo myVictoire 1.0.*@dev

Import the database in var/dump/db.sql

or do it yourself by doing the usual stuff (doctrine:schema:update etc). You can also load fixtures with the following lines :

    php bin/console doctrine:database:create
    php bin/console doctrine:schema:create
    php bin/console victoire:generate:view-cache --env=dev
    php bin/console doctrine:fixtures:load --fixtures="vendor/victoire/victoire/Bundle/CoreBundle/DataFixtures/ORM"


## Manually

- Install Victoire core  :

    composer require victoire/victoire

- Declare in AppKernel:

```php
    //Victoire bundles
    new Victoire\Bundle\AnalyticsBundle\VictoireAnalyticsBundle(),
    new Victoire\Bundle\CoreBundle\VictoireCoreBundle(),
    new Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
    new Victoire\Bundle\BusinessEntityPageBundle\VictoireBusinessEntityPageBundle(),
    new Victoire\Bundle\FormBundle\VictoireFormBundle(),
    new Victoire\Bundle\FilterBundle\VictoireFilterBundle(),
    new Victoire\Bundle\MediaBundle\VictoireMediaBundle(),
    new Victoire\Bundle\PageBundle\VictoirePageBundle(),
    new Victoire\Bundle\QueryBundle\VictoireQueryBundle(),
    new Victoire\Bundle\SeoBundle\VictoireSeoBundle(),
    new Victoire\Bundle\SitemapBundle\VictoireSitemapBundle(),
    new Victoire\Bundle\ThemeBundle\VictoireThemeBundle(),
    new Victoire\Bundle\TwigBundle\VictoireTwigBundle(),
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
        "appventus/text-widget"      : "dev-master",
        "appventus/redactor-widget"  : "dev-master",
        "appventus/button-widget"    : "dev-master",
        "appventus/image-widget"     : "dev-master",
        "appventus/render-widget"    : "dev-master",
        "appventus/breadcrumb-widget": "dev-master",
        ...
```

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
- Login with anakin@victoire.io:test user and start creating your website

