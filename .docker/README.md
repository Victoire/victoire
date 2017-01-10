Docker environement
===================

##Linux##

  * Ensure you have the latest `docker engine` installed. Your distribution's package might be a little old, if you encounter problems, do upgrade. See https://docs.docker.com/engine/installation/
  * Ensure you have the latest `docker-compose` installed. See [docs.docker.com/compose/install](https://docs.docker.com/compose/install/)
  
Once you're done, simply `cd .docker`, then `docker-compose -f docker-compose.yml -f docker-compose.setup.yml up -d`. This will initialise and start all the containers, then leave them running in the background.
Later, if you want to reboot the environement without re-setuping the database, redis cache, assets, run just `docker-compose up -d` 


Victoire is securized by default, credentials are anakin@victoire.io / test
  
##MAC OS##

Under MACOS, the docker architecture will work but with poor performances.

After setup the environement with `docker-compose -f docker-compose.yml -f docker-compose.setup.yml up`, you can use the alternative sync system provided.
To run it, just use `docker-compose -f docker-compose.yml -f docker-compose-mac.yml up -d`

rsync is provided by https://github.com/EugenMayer/docker-sync

## Tests ##

Selenium is packed in the docker-compose configuration. To run a test, you can execute `docker exec -it victoire-php /var/www/victoire/vendor/bin/behat -c /var/www/victoire/.docker/php-fpm/behat.yml`

Sometimes, the chrome container can't start with this error: "Waiting xvfb". It is probable that the X99 lock file is not deleted, just run `docker exec -it docker_chrome_1 rm /tmp/.X99-lock` and try to compose up again  


##Services exposed outside your environment##

You can access Victoire via **`127.0.0.1:8000`** . nginx and mailhog respond to any hostname, in case you want to add your own hostname on your `/etc/hosts` 

Service|Address
------|---------
Webserver|[127.0.0.1:8000](http://127.0.0.1:8000)
Mailhog web interface|[127.0.0.1:8001](http://127.0.0.1:8001)

##Hosts within your environment##

You'll need to configure your application to use any services you enabled:

Service|Hostname|Port number
------|---------|-----------
php-fpm|victoire-php-fpm|9000
MySQL|victoire-mysql|3306 (default)
Redis|victoire-redis|6379 (default)
SMTP (Mailhog)|victoire-mailhog|1025 (default)

#Docker compose cheatsheet#

**Note 1:** you need to cd first to where your docker-compose.yml file lives

  * Start containers in the background: `docker-compose up -d`
  * Start containers on the foreground: `docker-compose up`. You will see a stream of logs for every container running.
  * Stop containers: `docker-compose stop`
  * Kill containers: `docker-compose kill`
  * View container logs: `docker-compose logs`
  * Execute command inside of container: `docker exec -it victoire-php-fpm COMMAND` where `COMMAND` is whatever you want to run. For instance, `/bin/bash` to open a console prompt.
