# System Requirements

* A webserver like Apache or Nginx
* PHP from 5.4
    * JSON needs to be enabled
    * ctype needs to be enabled
    * php.ini recommended settings
        * short_open_tag = Off
        * magic_quotes_gpc = Off
        * register_globals = Off
        * session.auto_start = Off
        * date.timezone should be configured
* MySQL 5.x
* Redis from 2.0 to 3.2
* Less 2.x

# Composer

| Name                                 | Version      | Comment                     |
| -------------------------------------|--------------|---------------------------- |
| php                                  | >=5.4        | Traits                      |
| a2lix/translation-form-bundle        | ^2.1         | Stable version start at 2.1 |
| doctrine/doctrine-fixtures-bundle    | ^2.2         | Stable version start at 2.2 |
| doctrine/doctrine-migrations-bundle  | ^1.1         | Framework 2.3               |
| doctrine/orm                         | ^2.5         |                             |
| friendsofsymfony/jsrouting-bundle    | ^1.0 or ^2.0 |                             |
| friendsofsymfony/user-bundle         | ~2.0@dev     | 2.0 is not yet stable       |
| friendsofvictoire/text-widget        | ^2.0         |                             |
| friendsofvictoire/button-widget      | ^2.0         |                             |
| incenteev/composer-parameter-handler | ^2.0         |                             |
| jms/serializer-bundle                | ^1.0         |                             |
| knplabs/gaufrette                    | ^0.1         |                             |
| knplabs/doctrine-behaviors           | ^1.1         |                             |
| knplabs/knp-menu-bundle              | ^2.1         |                             |
| liip/imagine-bundle                  | ^1.4         |                             |
| predis/predis                        | ^1.1         |                             |
| sensio/distribution-bundle           | ^2.3         |                             |
| sensio/framework-extra-bundle        | ~3.0         |                             |
| snc/redis-bundle                     | ~2.0         |                             |
| stof/doctrine-extensions-bundle      | ~1.2         |                             |
| symfony/assetic-bundle               | ~2.3         |                             |
| symfony/monolog-bundle               | ~2.4 or ~3.0 |                             |
| symfony/symfony                      | ^2.8         |                             |
| symfony/swiftmailer-bundle           | ^2.3         |                             |
| troopers/alertify-bundle             | ^3.0         |                             |
| troopers/assetic-injector-bundle     | ^1.0         |                             |
| twig/extensions                      | ~1.0         |                             |
| twig/twig                            | ~1.0         |                             |
| willdurand/js-translation-bundle     | ^2.5         |                             |
