
ENV ?= dev
SYMFONY = php Tests/App/bin/console --env="$(ENV)"

vendor: ## Runs composer
vendor: composer.lock
	composer install

composer.lock: composer.json
	composer update

install: ## Setup vendors, assets, database and fixtures
install: vendor cc bower js-trans js-route assetic db fixtures vgv

start: ## 
start: redis vendor
	$(SYMFONY) server:run 0.0.0.0:8000  --docroot=Tests/App/web/

assets: vendor
	$(SYMFONY) assets:install Tests/App/web

assetic: assets vendor
	$(SYMFONY) assetic:dump

bower: Bundle/UIBundle/Resources/public/bower_components

Bundle/UIBundle/Resources/public/bower_components: Bundle/UIBundle/Resources/config/bower.json
	(cd Bundle/UIBundle/Resources/config/ && bower install)

db: vendor
	-$(SYMFONY) doctrine:database:drop --if-exists --force
	-$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:schema:create

fixtures:
	$(SYMFONY) doctrine:fixtures:load --fixtures=Tests/App/src/Acme/AppBundle/DataFixtures/Seeds/ORM/ -n

cc: vendor
	$(SYMFONY) cache:clear

vgv: redis vendor
	$(SYMFONY) victoire:generate:view

Tests/App/web/js/translations/config.js: vendor
	$(SYMFONY) bazinga:js-translation:dump

js-trans: Tests/App/web/js/translations/config.js

Tests/App/web/js/fos_js_routes_$(ENV).js: vendor
	$(SYMFONY) fos:js-routing:dump --target="Tests/App/web/js/fos_js_routes_$(ENV).js"

js-route: Tests/App/web/js/fos_js_routes_$(ENV).js

test:
	php ./vendor/bin/behat $(arg)

redis:
	-docker run -p 6379:6379 redis:3.2


.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help