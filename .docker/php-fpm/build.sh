cd /var/www/victoire \
    && mkdir -p /tmp/Victoire/cache/ /tmp/Victoire/logs/ \
    && chmod -R 777 /tmp/Victoire/cache/ /tmp/Victoire/logs/ \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer install \
    && php Tests/App/bin/console --env=docker cache:warmup \
    && php Tests/App/bin/console --env=docker do:sc:up --force \
    && php Tests/App/bin/console --env=docker do:fi:lo --fixtures=Tests/App/src/Acme/AppBundle/DataFixtures/Seeds/ORM -n \
    && php Tests/App/bin/console --env=docker victoire:generate:view \
    && php Tests/App/bin/console --env=docker assets:install Tests/App/web \
    && php Tests/App/bin/console --env=docker bazinga:js-translation:dump \
    && php Tests/App/bin/console --env=docker fos:js:dump --target="Tests/App/web/js/fos_js_routes_test.js" \
    && php Tests/App/bin/console --env=docker assetic:dump
