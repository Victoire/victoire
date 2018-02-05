# Backend contribution

## Setup

Requirements:

- Redis
- Docker

Create a `behat.yml` file in order to disable `Victoire\Tests\Features\Context\CoverageContext`:

```yml
imports:
    - "behat.yml.dist"

default:
    suites:
        default:
            contexts:
                - Knp\FriendlyContexts\Context\EntityContext
                - Knp\FriendlyContexts\Context\AliceContext
                - Victoire\Tests\Features\Context\VictoireContext
                #- Victoire\Tests\Features\Context\CoverageContext
                - Victoire\Tests\Features\Context\FeatureContext
                - Victoire\Tests\Features\Context\JavascriptContext
                - Victoire\Tests\Features\Context\MinkContext
                - Knp\FriendlyContexts\Context\TableContext
    extensions:
        Behat\MinkExtension\ServiceContainer\MinkExtension:
            base_url: 'http://anakin@victoire.io:test@172.17.0.1:8000/app_ci.php'
```

Copy the `parameters.yml` file:

```bash
cp Tests/App/app/config/parameters.yml.dist Tests/App/app/config/parameters.yml
```

Then configure database credentials in the `Tests/App/app/config/parameters.yml` file.

Setup the environment:

```bash
composer install --no-progress
(cd Bundle/UIBundle/Resources/config/ && bower install)
php Tests/App/bin/console --env=ci doctrine:database:drop --force
php Tests/App/bin/console --env=ci doctrine:database:create
php Tests/App/bin/console --env=ci cache:warmup
php Tests/App/bin/console --env=ci doctrine:schema:create
php Tests/App/bin/console --env=ci victoire:generate:view
php Tests/App/bin/console --env=ci bazinga:js-translation:dump
php Tests/App/bin/console --env=ci fos:js-routing:dump --target="Tests/App/web/js/fos_js_routes_test.js"
php Tests/App/bin/console --env=ci assets:install Tests/App/web
php Tests/App/bin/console --env=ci assetic:dump
mkdir -p fails/
```

## Launch processes

Launch Selenium:

```bash
docker run -d -p 4444:4444 selenium/standalone-firefox:2.53.1
```

Launch the Symfony built-in server:

```bash
php Tests/App/bin/console --env=ci server:run 0.0.0.0:8000 -r Tests/App/app/config/router_ci.php --docroot=Tests/App/web/
```

## Load fixtures and run tests

And finally, run tests with Behat:

```bash
php ./vendor/bin/behat
```

Once a test has been launched, users fixtures have been loaded, you can connect to http://localhost:8000/app_ci.php/

Username is `anakin@victoire.io` and password is `test`.   

You can also access to the Symfony Profiler on http://localhost:8000/app_ci.php/_profiler/
