FROM phpdockerio/php7-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && curl -sL https://deb.nodesource.com/setup_0.12 | bash - \
    && apt-get -y --no-install-recommends install  php7.0-mysql php7.0-redis php7.0-xdebug nodejs \
    && npm install -g less \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
    && echo 'alias vicsf="php /var/www/victoire/Tests/App/bin/console --env=docker "' >> ~/.bashrc \
    && echo 'alias run-behat="/var/www/victoire/vendor/bin/behat -c /var/www/victoire/.docker/php-fpm/behat.yml"' >> ~/.bashrc

WORKDIR "/var/www/victoire"
