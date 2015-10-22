#UPGRADE 1.2.2

In your base.html.twig
- Change the ng-init of your <body> tag by using ng-init="init({% if view is defined %}'{{ view.cssHash }}'{% endif %})"
- Add {{ cms_page_css() }} Twig extension at the beginning of your <body> tag
