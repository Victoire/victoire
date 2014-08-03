- Installer le coeur de victoire
composer require victoire/victoire

- Déclarer dans AppKernel:

            //Victoire bundles
            new Victoire\Bundle\CoreBundle\VictoireCoreBundle(),
            new Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
            new Victoire\Bundle\BusinessEntityTemplateBundle\VictoireBusinessEntityTemplateBundle(),
            new Victoire\Bundle\MediaBundle\VictoireMediaBundle(),
            new Victoire\Bundle\QueryBundle\VictoireQueryBundle(),
            new Victoire\Bundle\ThemeBundle\VictoireThemeBundle(),
            new Victoire\Bundle\DashboardBundle\VictoireDashboardBundle(),
            new Victoire\Bundle\FormBundle\VictoireFormBundle(),
            new Victoire\Bundle\PageBundle\VictoirePageBundle(),
            new Victoire\Bundle\SeoBundle\VictoireSeoBundle(),

- créer le fichier de config  victoire.yml suivant

        victoire_core:
            user_class: APE\AppBundle\Entity\User\User
            applicative_bundle: AppBundle
            templates:
                layout: "::layout.html.twig"
            layouts:
                fullWidth: "Contenu unique"
            slots:
                header_logo:
                    max: 1
                    widgets:
                        image: ~
                header_top_link:
                    max: 1
                    widgets:
                        button: ~
                        redactor: ~
                header_col1:
                    max: 1
                    widgets:
                        redactor: ~
                        inputsearchform: ~
                header_col2:
                    max: 1
                    widgets:
                        button: ~
                        redactor: ~
                header_col3:
                    max: 1
                    widgets:
                        button: ~
                        redactor: ~
                header_col4:
                    max: 1
                    widgets:
                        button: ~
                        redactor: ~

                footer_col1:
                    widgets:
                        redactor: ~
                footer_col2:
                    widgets:
                        redactor: ~
                footer_col3:
                    widgets:
                        redactor: ~
                footer_sub_col1:
                    widgets:
                        redactor: ~
                footer_sub_col2:
                    widgets:
                        redactor: ~
                footer_sub_col3:
                    widgets:
                        redactor: ~
                footer_bottom:
                    max: 1
                    widgets:
                        redactor: ~
                main_content:
                    widgets:
                        render: ~
                        redactor: ~
                        button: ~
                        image: ~
                breadcrumb:
                    max: 1
                    widgets:
                        breadcrumb: ~
                dashboard_menu:
                    widgets:
                        redactor: ~
                        dashboardmenu: ~
                top_fullWidth_content:
                    widgets:
                        redactor: ~
                        render: ~
                bottom_fullWidth_content:
                    widgets:
                        render: ~


- ajouter les widgets requis:

            "victoire/text-widget": "dev-master",
            "victoire/redactor-widget": "dev-master",
            "victoire/button-widget": "dev-master",
            "victoire/image-widget": "dev-master",
            "victoire/render-widget": "dev-master",
            "victoire/breadcrumb-widget": "dev-master »,


Vérifier les dépendances de victoire:

        "knplabs/knp-menu"                       : "2.0.0-alpha1",
        "knplabs/knp-menu-bundle"                : "2.0.0-alpha1 »,
        "friendsofsymfony/user-bundle"           : "dev-master »,
        "appventus/assetic-injector-bundle"      : "dev-master »,
        "appventus/alertify-bundle"              : "dev-master »,
        "appventus/shortcuts-bundle"             : "dev-master",

        "knplabs/gaufrette"                      : "v0.1.7",
        "knplabs/knp-gaufrette-bundle"           : "v0.1.7",
        "knplabs/knp-components"                 : "dev-master",
- mise à jour de la base

        php app/console do:sc:up —force

- Ajoutez le folder de base du media bundle avec cette instruction sql :

```sql
INSERT INTO `vic_media_folder` (`id`, `parent_id`, `name`, `created_at`, `updated_at`, `rel`, `internal_name`, `deleted`)
VALUES
    (1, NULL, '/', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, NULL, 0);

```


- setup routing:

        VictoireBlogBundle:
            resource: "@VictoireBlogBundle/Controller/"
            type:     annotation
            prefix:   /
        VictoireBusinessEntityTemplateBundle:
            resource: "@VictoireBusinessEntityTemplateBundle/Controller/"
            type:     annotation
            prefix:   /
        VictoirePageBundle:
            resource: "@VictoirePageBundle/Controller/"
            type:     annotation
            prefix:   /

        VictoireCmsBundle:
            resource: "@VictoireCoreBundle/Resources/config/routing.yml"
            prefix:   /


- Enable StofDoctrineExtensions in AppKernel

- add add this config in doctrine.yml:

        orm:
           [...]
            mappings:
                gedmo_tree:
                    type: annotation
                    prefix: Gedmo\Tree\Entity
                    dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Tree/Entity"
                    is_bundle: false

- add this config in config.yml
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

- Aller sur «  /victoire-dcms/dashboard/welcome » en étant connecté avec un user ayant le rôle ROLE_VICTOIRE

