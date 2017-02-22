#How to install Victoire DCMS?

You actually have two ways to install it:

## 1. From the Victoire Demo

Follow the instructions on [victoire demo Readme](https://github.com/Victoire/demo/blob/master/README.md)

## 2. From scratch

```sh
symfony new . 2.8
```

## Victoire and its dependencies

### Composer
```
composer require victoire/victoire \
    friendsofsymfony/user-bundle:~2.0@dev \
    doctrine/orm \
    --update-with-dependencies
```

### AppKernel

Register the following bundles in the `AppKernel`:

`app/config/AppKernel.php`
```php
<?php
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            ...
            //dependencies
            new Troopers\AsseticInjectorBundle\TroopersAsseticInjectorBundle(),
            new Troopers\AlertifyBundle\TroopersAlertifyBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
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
            new Victoire\Bundle\UIBundle\VictoireUIBundle(),
            new Victoire\Bundle\UserBundle\VictoireUserBundle(),
            new Victoire\Bundle\ViewReferenceBundle\ViewReferenceBundle(),
            new Victoire\Bundle\WidgetBundle\VictoireWidgetBundle(),
            new Victoire\Bundle\WidgetMapBundle\VictoireWidgetMapBundle(),
            ...
        ];
    }
```

### Start a redis server

[Quick start](https://redis.io/topics/quickstart)

just start it with docker:
```sh
docker run -d -p 6385:6379 --name myAwesomeRedis redis:latest
```

### Config

#### Enable the serializer and translator

`app/config/config.yml`
```yml
framework:
   ...
   translator: { fallbacks: ["%locale%"] }
   serializer: { enable_annotations: true }
```

#### Add some config

`app/config/config.yml`
```yml
imports:
    - { resource: @VictoireCoreBundle/Resources/config/config.yml }

assetic:
    use_controller: false
    bundles: ~
    filters:
        cssrewrite: ~
        less: ~ #Victoire needs less under v3.0.0

fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Victoire\UserBundle\Entity\User
    from_email:
        address: hey@victoire.io
        sender_name: Victoire

snc_redis:
    clients:
        victoire:
            type: predis
            alias: victoire
            dsn: %victoire_redis_path%
            logging: false

stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            tree: true
            sluggable: true
            timestampable: true

victoire_core:
    user_class: "Victoire\\UserBundle\\Entity\\User"
    business_entity_debug: true
    layouts:
        defaultLayout: "Default layout"

#if you need i18n
victoire_i18n:
    available_locales:
        - fr
        - en
    locale_pattern: parameter #domain

    #if locale_pattern is domain, then you'll need to define the following parameter:
    #locale_pattern_table: %locale_pattern_table%
```

`app/config/security.yml`
```
security:
    encoders:
        Victoire\Bundle\UserBundle\Entity\User: bcrypt
    providers:
        fos_userbundle:
            id: fos_user.user_provider.username
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            pattern: ^/
            form_login:
                provider: fos_userbundle
                csrf_provider: form.csrf_provider
                failure_path: /login
                check_path: /login_check
                default_target_path: /
            logout: true
            anonymous: true
            switch_user: ~

    access_control:
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
```

`app/config/services.yml`
```
services:
    twig.extension.text:
       class: Twig_Extensions_Extension_Text
       tags:
            - { name: twig.extension }

    twig.extension.intl:
        class: Twig_Extensions_Extension_Intl
        tags:
            - { name: twig.extension }
```

`app/config/parameters.yml.dist`
```
parameters:
    ...
    fos_js_base_url: https://victoire.io
    victoire_redis_path: redis://127.0.0.1:6379
    #if locale_pattern is domain
    locale_pattern_table:
        io.victoire.dev: fr
        victoire.io: fr
```

Update the `parameters.yml` with correct values.

```yml
#app/config/config.yml
imports:
    ...
    - { resource: victoire_core.yml }
```

### Add following routes

```yml
#app/config/routing.yml
_bazinga_jstranslation:
    resource: "@BazingaJsTranslationBundle/Resources/config/routing/routing.yml"

fos_js_routing:
    resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

#Needs to be the last
VictoireCoreBundle:
    resource: .
    type: victoire_i18n
```

Then you're done with the Victoire steps but your database is empty. Just run these commands to get seeds:

Start by creating your admin user:
```sh
bin/console -e=dev fos:user:create admin anakin@victoire.io myAwesomePassword
bin/console -e=dev fos:user:promote admin ROLE_VICTOIRE_DEVELOPER
```

Then run these sql queries to populates the initial views (error pages, one base template and the homepage):
```sql
INSERT INTO `vic_view` (`id`, `parent_id`, `template_id`, `author_id`, `seo_id`, `bodyId`, `bodyClass`, `position`, `lft`, `lvl`, `rgt`, `root`, `undeletable`, `cssHash`, `widget_map`, `cssUpToDate`, `roles`, `created_at`, `updated_at`, `type`, `author_restricted`, `backendName`, `query`, `orderBy`, `business_entity_id`, `entityProxy_id`, `status`, `publishedAt`, `homepage`, `layout`, `code`)
VALUES
    (1, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 7, 1, NULL, 'a:0:{}', 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'template', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'defaultLayout', NULL),
    (2, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 1, 1, NULL, 'a:0:{}', 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 400),
    (3, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 2, 1, NULL, 'a:0:{}', 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 403),
    (4, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 3, 1, NULL, 'a:0:{}', 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 404),
    (5, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 4, 1, NULL, 'a:0:{}', 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 500),
    (6, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 5, 1, NULL, 'a:0:{}', 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 503),
    (7, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 76, 6, 1, NULL, 'a:0:{}', 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'page', NULL, NULL, NULL, NULL, NULL, NULL, 'published', '2017-01-01 00:00:00', 1, NULL, NULL);

INSERT INTO `vic_view_translations` (`id`, `translatable_id`, `name`, `slug`, `locale`)
VALUES
    (1, 1, 'Base Template', 'base-template', 'en'),
    (2, 1, 'Template de base', 'template-de-base', 'fr'),
    (3, 2, 'Page not found', 'error-400', 'en'),
    (4, 2, 'Page introuvable', 'erreur-400', 'fr'),
    (5, 3, 'Forbidden', 'error-403', 'en'),
    (6, 3, 'Interdit', 'erreur-403', 'fr'),
    (7, 4, 'Page not Found', 'error-404', 'en'),
    (8, 4, 'Page introuvable', 'erreur-404', 'fr'),
    (9, 5, 'Internal Error', 'error-500', 'en'),
    (10, 5, 'Erreur interne', 'erreur-500', 'fr'),
    (11, 6, 'Service unavailable', 'error-503', 'en'),
    (12, 6, 'Service indisponible', 'erreur-503', 'fr'),
    (13, 7, 'Homepage', 'home', 'en'),
    (14, 7, 'Page d\'accueil', 'accueil', 'fr');
```

### Generate view references

```sh
php bin/console victoire:viewReference:generate -e=dev
```

#### Do you prefer the fixtures way ?
There are some fixtures in `vendor/victoire/victoire/Tests/Functionnal/src/Acme/AppBundle/DataFixtures/Seeds/ORM/LoadFixtureData.php`. These are used in the victoire behat tests so you can't use them directly from your project but feel free to start from it by copying/pasting it into your own project.

### Add the wanted widgets:

```json
    "friendsofvictoire/text-widget"      : "~2.0",
    "friendsofvictoire/button-widget"    : "~2.0",
    "friendsofvictoire/image-widget"     : "~2.0",
    "friendsofvictoire/render-widget"    : "~2.0",
    "friendsofvictoire/breadcrumb-widget": "~2.0",
    ...
```

Get the whole Victoire Widget list [**here**](http://packagist.org/search/?tags=victoire)


### Prepare Victoire assets

#### Fetch bower assets

Run the following command to fetch the Victoire assets:

`CAUTION` you need to install bower first
```shell
php bin/console victoire:ui:fetchAssets
```

#### Dump with assetic

Run the following command to dump assets with assetic library:

```shell
php bin/console assetic:dump
```


**And it's done, just go to /login to enter in the edit mode.**

## 3. Production

### Views CSS

When you edit a Widget style parameter in Victoire, CSS rules must be generated and imported in concerned View.
Unfortunately, Victoire can't simply include inline `<style>` tags for each Widget due to some [IE restrictions][1].  
That's why for each View, a CSS file is generated compiling all Widgets CSS rules.

When a Widget which belongs to a Template is modified, all inherited Templates and Pages CSS files must be regenerated. Files can be regenerated :

* **on-the-fly** when a user ask for a View that need to regenerate its CSS
* **with command** `victoire:widget-css:generate`

So you can let Victoire regenerate CSS files on user demand.
But you may want to set a crontab on your production environment to regenerate a batch of CSS files each minute.

```
* * * * * php bin/console victoire:widget-css:generate --limit=20 --env=prod
```

If you want to manually force all CSS to be regenerated even if they are up to date, add `--force`.

```sh
php bin/console victoire:widget-css:generate --force
```

[1]: https://blogs.msdn.microsoft.com/ieinternals/2011/05/14/stylesheet-limits-in-internet-explorer/