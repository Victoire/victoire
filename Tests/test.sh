#!/bin/sh
#curl -s http://getcomposer.org/installer | php
#php -d memory_limit=-1 composer.phar install --dev --prefer-dist
php Tests/App/bin/console --env=test doctrine:database:drop --force
php Tests/App/bin/console --env=test doctrine:database:create
php Tests/App/bin/console --env=test cache:warmup
php Tests/App/bin/console --env=test doctrine:schema:create
php Tests/App/bin/console --env=test victoire:generate:view
php Tests/App/bin/console --env=test bazinga:js-translation:dump
php Tests/App/bin/console --env=test assets:install Tests/App/web
php Tests/App/bin/console --env=test assetic:dump
nohup php Tests/App/bin/console --env=test server:run -r vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/config/router_prod.php &
sleep 5
curl http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.0.jar > selenium-server-standalone-2.53.0.jar
nohup java -jar selenium-server-standalone-2.53.0.jar > /dev/null &
php Tests/App/bin/console fos:js-routing:dump --env=test --target="Tests/App/web/js/fos_js_routes_test.js"
php Tests/App/bin/console fos:js-routing:dump --env=domain --target="Tests/App/web/js/fos_js_routes_domain.js"
phpunit --coverage-text

#php ./vendor/bin/behat $1
