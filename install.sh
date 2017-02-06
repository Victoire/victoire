vicsf --env=test doc:sch:upd -f
vicsf --env=test ca:cl
vicsf --env=test ass:ins Tests/Functionnal/web/
vicsf --env=test baz:js:dum Tests/Functionnal/web/js/
vicsf --env=test fos:js:dum --target="Tests/Functionnal/web/js/fos_js_routes_test.js"
vicsf --env=test ass:dum
vicsf --env=test server:start 127.0.0.1:8080 -r vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/config/router_prod.php
curl http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar > selenium-server-standalone-2.53.1.jar
nohup java -jar selenium-server-standalone-2.53.1.jar > /dev/null &