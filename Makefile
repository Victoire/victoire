
ENV ?= dev
SYMFONY = `if [ $(ENV) != "dev" ]; then echo 'php Tests/App/bin/console --env=$(ENV) --no-debug'; else echo 'php Tests/App/bin/console --env=$(ENV)'; fi`

vendor: ## Runs composer install
vendor: composer.lock
	composer install

composer.lock: composer.json
	composer update

install: ## Setup vendors, assets, database and seeds
install: vendor cc bower js-trans js-route assetic db seeds vgv

start: ## Start built-in server
start: redis vendor
	@ if [ $(ENV) != "ci" ]; then \
		$(SYMFONY) server:run 0.0.0.0:8000  --docroot=Tests/App/web/; \
	else \
		$(SYMFONY) server:run 0.0.0.0:8000  --docroot=Tests/App/web/ -r Tests/App/app/config/router_ci.php; \
	fi

assetic: ## Install assets
assets: vendor
	$(SYMFONY) assets:install Tests/App/web

assetic: ## Dump assets
assetic: assets vendor
	$(SYMFONY) assetic:dump

bower: ## Install bower depedencies
bower: Bundle/UIBundle/Resources/public/bower_components

Bundle/UIBundle/Resources/public/bower_components: Bundle/UIBundle/Resources/config/bower.json
	(cd Bundle/UIBundle/Resources/config/ && bower install --allow-root)

db: ## Initialize a database
db: vendor
	-$(SYMFONY) doctrine:database:drop --if-exists --force
	-$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:schema:create

seeds: ## Load minimal seeds
	$(SYMFONY) doctrine:fixtures:load --fixtures=Tests/App/src/Acme/AppBundle/DataFixtures/Seeds/ORM/ -n

fixtures: ## Load test fixtures
	$(SYMFONY) doctrine:fixtures:load --fixtures=Tests/App/src/Acme/AppBundle/DataFixtures/Fixtures/ORM/ -n

cc: ## Clear symfony cache
cc: vendor
	$(SYMFONY) cache:clear

vgv: ## Generate Victoire views
vgv: redis vendor
	$(SYMFONY) victoire:generate:view

Tests/App/web/js/translations/config.js: vendor
	$(SYMFONY) bazinga:js-translation:dump

js-trans: ## Dump js translations
js-trans: Tests/App/web/js/translations/config.js

Tests/App/web/js/fos_js_routes_$(ENV).js: vendor
	$(SYMFONY) fos:js-routing:dump --target="Tests/App/web/js/fos_js_routes_$(ENV).js"

js-route: ## Dump js routing
js-route: Tests/App/web/js/fos_js_routes_$(ENV).js

test: ## Launch behat test suite
	php ./vendor/bin/behat $(arg)

redis: ## Start a redis threw docker
	-docker run -p 6379:6379 redis:3.2


.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help