#Setup

## Just press the button


    git clone https://github.com/Victoire/launchpad myVictoireWebsite

Import the database in var/dump/db.sql

or do it yourself by doing the usual stuff (doctrine:schema:update etc). You can also load fixtures with the following lines :

    php bin/console doctrine:databas:create
    php bin/console doctrine:schema:update -force
    php bin/console doctrine:fixtures:load --fixtures="vendor/victoire/victoire/Victoire/Bundle/CoreBundle/DataFixtures/ORM" -n


## Manuellement

- Installer le coeur de victoire :


    composer require victoire/victoire

- Déclarer dans AppKernel:


    //Victoire bundles
    new Victoire\Bundle\CoreBundle\VictoireCoreBundle(),
    new Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
    new Victoire\Bundle\BusinessEntityPageBundle\VictoireBusinessEntityPageBundle(),
    new Victoire\Bundle\MediaBundle\VictoireMediaBundle(),
    new Victoire\Bundle\QueryBundle\VictoireQueryBundle(),
    new Victoire\Bundle\ThemeBundle\VictoireThemeBundle(),
    new Victoire\Bundle\DashboardBundle\VictoireDashboardBundle(),
    new Victoire\Bundle\FormBundle\VictoireFormBundle(),
    new Victoire\Bundle\PageBundle\VictoirePageBundle(),
    new Victoire\Bundle\SeoBundle\VictoireSeoBundle(),
    new Victoire\Bundle\TwigBundle\VictoireTwigBundle(),


- créer le fichier de config  victoire.yml suivant


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

- ajouter les widgets requis:


        "appventus/text-widget"      : "dev-master",
        "appventus/redactor-widget"  : "dev-master",
        "appventus/button-widget"    : "dev-master",
        "appventus/image-widget"     : "dev-master",
        "appventus/render-widget"    : "dev-master",
        "appventus/breadcrumb-widget": "dev-master",
        ...


Vérifier les dépendances de victoire:


    "knplabs/knp-menu"                       : "2.0.0-alpha1",
    "knplabs/knp-menu-bundle"                : "2.0.0-alpha1",
    "friendsofsymfony/user-bundle"           : "dev-master",
    "appventus/assetic-injector-bundle"      : "dev-master",
    "appventus/alertify-bundle"              : "dev-master",
    "appventus/shortcuts-bundle"             : "dev-master",
    "knplabs/gaufrette"                      : "v0.1.7",
    "knplabs/knp-gaufrette-bundle"           : "v0.1.7",
    "knplabs/knp-components"                 : "dev-master",

- mise à jour de la base

        php bin/console do:sc:up —force

- Lancez les fixtures pour peupler la base de données

        php bin/console doctrine:fixtures:load --fixtures="vendor/victoire/victoire/Victoire/Bundle/CoreBundle/DataFixtures/ORM"

- setup routing:

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



- Enable StofDoctrineExtensions in AppKernel

- add this config in doctrine.yml:

        orm:
           [...]
            mappings:
                gedmo_tree:
                    type: annotation
                    prefix: Gedmo\Tree\Entity
                    dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Tree/Entity"
                    is_bundle: false

- add this config in stof_doctrine_extensions (imported in config.yml)
    stof_doctrine_extensions:
        default_locale: fr_FR
        orm:
            default:
                tree: true

- installer FOSJSRouting

- activer le filtre localizeddate:

        twig.extension.intl:
            class: Twig_Extensions_Extension_Intl
            tags:
                - { name: twig.extension }

- importer le fichier de config de victoire:
imports:
        - { resource: @VictoireCoreBundle/Resources/config/config.yml }

- Aller sur «  /victoire-dcms/dashboard/welcome" en étant connecté avec un user ayant le rôle ROLE_VICTOIRE

