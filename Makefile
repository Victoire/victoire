
ENV ?= dev
SYMFONY = php Tests/App/bin/console --env="$(ENV)"

vendor: composer.lock
	composer install


install: parameters bower assetic db cc js-trans js-route vgv


Tests/App/app/config/parameters.yml:
	@read -p "secret:" secret; \
	@read -p "dbname:" dbname; \
	@read -p "dbuser:" dbuser; \
	@read -p "dbpassword:" dbpassword; \
	@read -p "dbhost:" dbhost; \
	@read -p "redis-dns:" redis-dns; \

	cp Tests/App/app/config/parameters.yml.dist Tests/App/app/config/parameters.yml

parameters: Tests/App/app/config/parameters.yml


assets: vendor
	$(SYMFONY) assets:install Tests/App/web

assetic: assets vendor
	$(SYMFONY) assetic:dump

bower: Bundle/UIBundle/Resources/config/bower.json

Bundle/UIBundle/Resources/config/bower.json: Bundle/UIBundle/Resources/public/bower_components
	(cd Bundle/UIBundle/Resources/config/ && bower install)

db: parameters vendor
	-$(SYMFONY) doctrine:database:drop --if-exists --force
	-$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:schema:create

cc: parameters vendor
	$(SYMFONY) cache:clear

vgv: parameters vendor
	$(SYMFONY) victoire:generate:view

js-trans: parameters vendor
	$(SYMFONY) bazinga:js-translation:dump

Tests/App/web/js/fos_js_routes_$(ENV).js:  parameters vendor
	$(SYMFONY) fos:js-routing:dump --target="Tests/App/web/js/fos_js_routes_$(ENV).js"

js-route: Tests/App/web/js/fos_js_routes_$(ENV).js

.PHONY: install db js-route js-trans vgv cc bower assetic assets