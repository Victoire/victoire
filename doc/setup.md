# Setup


> If you have any question or doubt during the setup process, compare with this: https://github.com/Victoire/tutorial/tree/0744813baba719453e0673611c53d1d511133e65
> If you still have a question, feel free to contact us:

[![Twitter Follow](https://img.shields.io/twitter/follow/victoirecms.svg?style=social&label=Victoirecms)](https://twitter.com/troopersagency) [![Gitter](https://badges.gitter.im/Victoire/victoire.svg)](https://gitter.im/Victoire/victoire?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

```sh
symfony new tutorial 3.4.2
cd tutorial
```

## Victoire and its dependencies

### Composer

```
composer require victoire/victoire twig/twig:~2.0
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
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Troopers\AsseticInjectorBundle\TroopersAsseticInjectorBundle(),
            new Troopers\AlertifyBundle\TroopersAlertifyBundle(),
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
            new Victoire\Bundle\ConfigBundle\VictoireConfigBundle(),
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
I deleted : 
```new AppBundle\AppBundle(),```

### Start a redis server

[Quick start](https://redis.io/topics/quickstart)

just start it with docker:
```sh
docker run -d -p 6379:6379 --name myAwesomeRedis redis:latest
```

### Config

#### Enable the serializer and translator
### Add some config

`app/config/config.yml`
```yml
framework:
   ...
    translator: { fallbacks: ["%locale%"] }
    serializer: { enable_annotations: true }
    templating:
       engines: ['twig']

imports:
    ...
    - { resource: "@VictoireCoreBundle/Resources/config/config.yml" }
    - { resource: "@VictoireTwigBundle/Resources/config/config.yml" }

assetic:
    use_controller: false
    node: %node_path%
    node_paths: %node_paths%
    bundles: ~
    filters:
        cssrewrite: ~
        less: ~ #Victoire needs less under v3.0.0

fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Victoire\Bundle\UserBundle\Entity\User
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
    user_class: Victoire\Bundle\UserBundle\Entity\User
    business_entity_debug: true
    layouts:
        defaultLayout: "Default layout"
    # Here you need to list all folders containing your BusinessEntities
    # Remove friendsofvictoire if you don't need it
    base_paths:
        - "%kernel.root_dir%/../src"
        - "%kernel.root_dir%/../vendor/victoire/victoire/Bundle/BlogBundle"
        - "%kernel.root_dir%/../vendor/friendsofvictoire"

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
`Replace the current security.yml with this one`
```
# To get started with security, check out the documentation:
security:
    encoders:
        Victoire\Bundle\UserBundle\Entity\User: bcrypt
    providers:
        in_memory:
            memory: ~
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
                csrf_token_generator: security.csrf.token_manager
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
    ...
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
    fos_js_base_url: http://tutorial.victoire.dev #adjust to your need (of course)
    victoire_redis_path: redis://127.0.0.1:6379
    #if locale_pattern is domain
    locale_pattern_table:
        tutorial.victoire.dev: en

    node_path: /usr/local/bin/node
    node_paths:
        - /usr/local/lib/node_modules
        - /usr/local/share/npm/lib/node_modules
```
`Of course, Adjust to your needs`

### Add following routes

```yml
#app/config/routing.yml
_bazinga_jstranslation:
    resource: "@BazingaJsTranslationBundle/Resources/config/routing/routing.yml"

fos_user_security:
    resource: "@FOSUserBundle/Resources/config/routing/security.xml"

fos_user_resetting:
    resource: "@FOSUserBundle/Resources/config/routing/resetting.xml"
    prefix: /resetting

fos_js_routing:
    resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

#Needs to be the last
VictoireCoreBundle:
    resource: .
    type: victoire_i18n
```
If necessary, generate your missing parameters in parameters.yml:
```
composer install
```

As it never hurts, let's finish the configuration steps by clearing the cache:

```
bin/console -e=dev cache:clear
```

If you get this error: `Connection refused [tcp://127.0.0.1:6379]`, you probably choosed another port than the default one.

Then you're done with the Victoire steps but your database is empty. Just run these commands to get seeds:

Start by creating your admin user:
```sh
bin/console -e=dev doctrine:database:create
bin/console -e=dev doctrine:schema:create
bin/console -e=dev fos:user:create admin anakin@victoire.io myAwesomePassword
bin/console -e=dev fos:user:promote admin ROLE_VICTOIRE_DEVELOPER
```

Then run these sql queries to populates the initial views (error pages, one base template and the homepage):
```sql
INSERT INTO `vic_view` (`id`, `parent_id`, `template_id`, `author_id`, `seo_id`, `bodyId`, `bodyClass`, `position`, `lft`, `lvl`, `rgt`, `root`, `undeletable`, `cssHash`, `cssUpToDate`, `roles`, `created_at`, `updated_at`, `type`, `backendName`, `query`, `orderBy`, `business_entity_id`, `entityProxy_id`, `status`, `publishedAt`, `homepage`, `layout`, `code`)
VALUES
	(1, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 7, 1, NULL, 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'template', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'defaultLayout', NULL),
	(2, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 1, 1, NULL, 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 400),
	(3, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 2, 1, NULL, 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 403),
	(4, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 3, 1, NULL, 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 404),
	(5, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 4, 1, NULL, 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 500),
	(6, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 2, 5, 1, NULL, 1, NULL, '2017-01-01 00:00:00', '2017-01-01 00:00:00', 'errorpage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 503),
	(7, NULL, 1, NULL, NULL, NULL, NULL, 0, 1, 0, 80, 6, 1, '9895a77faa19f0078ff4e2c5808e1bc4321d5b2f', 1, NULL, '2017-01-01 00:00:00', '2017-05-21 11:15:04', 'page', NULL, NULL, NULL, NULL, NULL, 'published', '2017-01-01 00:00:00', 1, NULL, NULL),
	(8, 7, 1, 1, NULL, NULL, NULL, 2, 76, 1, 77, 6, 0, NULL, 0, NULL, '2017-05-21 20:30:44', '2017-05-21 20:30:44', 'blog', NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, NULL, NULL),
	(9, 7, 1, 1, NULL, NULL, NULL, 3, 78, 1, 79, 6, 0, '886e010d9b4f9cd648efb6a6cf838d1d02ee943e', 1, NULL, '2017-05-21 20:33:22', '2017-05-21 21:12:06', 'blog', NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, NULL, NULL);

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
	(14, 7, 'Page d\'accueil', 'accueil', 'fr'),
	(15, 9, 'My blog', 'my-blog', 'en'),
	(16, 9, 'Mon blog', 'mon-blog', 'fr');

INSERT INTO `vic_media_folders` (`id`, `parent_id`, `name`, `created_at`, `updated_at`, `rel`, `internal_name`, `deleted`)
VALUES
	(1, NULL, '/', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, NULL, 0);
```

### Generate view references

```sh
bin/console victoire:viewReference:generate -e=dev
```

#### Do you prefer the fixtures way ?
There are some fixtures in `vendor/victoire/victoire/Tests/App/src/Acme/AppBundle/DataFixtures/Seeds/ORM/LoadFixtureData.php`. These are used in the victoire behat tests so you can't use them directly from your project but feel free to start from it by copying/pasting it into your own project.

### Add the wanted widgets:

```sh
    composer require victoire/text-widget victoire/button-widget victoire/image-widget victoire/render-widget victoire/breadcrumb-widget ...
```

Don't forget to enable these widget in the AppKernel. To keep the previous example:

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Victoire\Widget\TextBundle\VictoireWidgetTextBundle(),
            new Victoire\Widget\ButtonBundle\VictoireWidgetButtonBundle(),
            new Victoire\Widget\ImageBundle\VictoireWidgetImageBundle(),
        );

        return $bundles;
    }
}
```
And update your schema:
```
bin/console -e=dev doctrine:schema:update --force
```

If you have the following warning:
```
The given path "../vendor/friendsofvictoire" seems to be incorrect. You need to edit victoire_core.base_paths configuration.
Updating database schema...
```
Don't worry: it will disapear as soon as you will install some non-official widgets (hosted in the `friendsofvictoire` organization.

Find others widget [**on packagist**](http://packagist.org/search/?tags=victoire)

### Prepare Victoire assets

#### Fetch front assets (thanks to bower and yarn)

Run the following command to fetch the Victoire assets:

`CAUTION` you need to install bower and yarn first
```shell
bin/console victoire:ui:fetchAssets --force
```

#### Dump js routes and translations

```
bin/console fos:js-routing:dump -e=dev
bin/console bazinga:js-translation:dump -e=dev
```

#### Dump with assetic

Run the following command to dump assets with assetic library:

`CAUTION` you need to install less first ```npm i -g less```
```shell
bin/console assetic:dump
```

**And it's done**, you should have a white screen with the Homepage title.
You just need to go to **/login** to enter in the edit mode. Do you remember the credentials used in the command line ?
That was `admin` and `myAwesomePassword`.

If after the login, you still have the default Symfony page, you probably need to remove the default route in `app/config/routing.yml` or be sure there isn't any conflictual route.

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
* * * * * bin/console victoire:widget-css:generate --limit=20 --env=prod
```

If you want to manually force all CSS to be regenerated even if they are up to date, add `--force`.

```sh
bin/console victoire:widget-css:generate --force
```

[1]: https://blogs.msdn.microsoft.com/ieinternals/2011/05/14/stylesheet-limits-in-internet-explorer/
