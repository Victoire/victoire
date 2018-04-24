Docker environment
===================

## Linux

  * Ensure you have the latest `docker engine` installed. Your distribution's package might be a little old, if you encounter problems, do upgrade. See https://docs.docker.com/engine/installation/
  * Ensure you have the latest `docker-compose` installed. See [docs.docker.com/compose/install](https://docs.docker.com/compose/install/)
  
Once you're done, simply `cd .docker`, then `docker-compose -f docker-compose.yml -f docker-compose.setup.yml up`. This will initialise and start all the containers, then leave them running.
Once the installation is complete, you will see `victoire-setup exited with code 0`.

Later, if you want to reboot the environment without re-setuping the database, redis cache, assets, run just `docker-compose up -d` 

Victoire is securized by default, credentials are anakin@victoire.io / test
  
## MAC OS

Under MACOS, the docker architecture will work but with poor performances.
After setup the environment with `docker-compose -f docker-compose.yml -f docker-compose.setup.yml up`, you can use the alternative sync system provided.
To run it, just use `docker-compose -f docker-compose.yml -f docker-compose-mac.yml up -d`

rsync is provided by https://github.com/EugenMayer/docker-sync

## Services exposed outside your environment

You can access Victoire via **`127.0.0.1:8000`**.

And be logged in automatically http://anakin@victoire.io:test@localhost:8000/

nginx and mailhog respond to any hostname, in case you want to add your own hostname on your `/etc/hosts` 

Service|Address
------|---------
Webserver|[127.0.0.1:8000](http://127.0.0.1:8000)
Mailhog web interface|[127.0.0.1:8001](http://127.0.0.1:8001)

## Hosts within your environment

You'll need to configure your application to use any services you enabled:

Service|Hostname|Port number
------|---------|-----------
php-fpm|victoire-php|9000
MySQL|victoire-mysql|3306 (default)
Redis|victoire-redis|6379 (default)
SMTP (Mailhog)|victoire-mailhog|1025 (default)
