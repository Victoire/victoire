#!/bin/sh
curl -s http://getcomposer.org/installer | php
composer.phar install --dev --prefer-dist
php Tests/Functionnal/bin/console --env=test doctrine:database:drop --force
php Tests/Functionnal/bin/console --env=test doctrine:database:create
php Tests/Functionnal/bin/console --env=test cache:warmup
php Tests/Functionnal/bin/console --env=test doctrine:schema:create
php Tests/Functionnal/bin/console --env=test victoire:generate:view
php Tests/Functionnal/bin/console --env=test assets:install Tests/Functionnal/web
php Tests/Functionnal/bin/console --env=test assetic:dump
nohup php Tests/Functionnal/bin/console --env=test server:run -r vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/config/router_prod.php &
sleep 5
curl http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar > selenium-server-standalone-2.45.0.jar
nohup java -jar selenium-server-standalone-2.45.0.jar > /dev/null &
phpunit --coverage-text

php ./vendor/bin/behat $1
