cd /var/www/victoire \
    && mkdir -p /tmp/Victoire/cache/ /tmp/Victoire/logs/ \
    && chmod -R 777 /tmp/Victoire/cache/ /tmp/Victoire/logs/ \
    && php Tests/Functionnal/bin/console --env=docker cache:warmup \
    && php Tests/Functionnal/bin/console do:sc:up --force -e docker\
    && php Tests/Functionnal/bin/console --env=docker do:fi:lo --fixtures=Tests/Functionnal/src/Acme/AppBundle/DataFixtures/seeds/ORM -n \
    && php Tests/Functionnal/bin/console --env=docker victoire:generate:view \
    && php Tests/Functionnal/bin/console --env=docker assets:install Tests/Functionnal/web \
    && php Tests/Functionnal/bin/console --env=docker bazinga:js-translation:dump \
    && php Tests/Functionnal/bin/console --env=docker fos:js:dump --target="Tests/Functionnal/web/js/fos_js_routes_test.js" \
    && php Tests/Functionnal/bin/console --env=docker assetic:dump
