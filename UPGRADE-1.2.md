#UPGRADE 1.2.0

- Replace in your project the following terms :


|order | OLD  | NEW  |
|---|---|---|
|\#1| BusinessEntityPagePattern  | BusinessTemplate  |
|\#2| BusinessEntityPage  | BusinessPage  |

- Run these sql queries:

```
UPDATE `vic_view` SET type = 'businesspage' WHERE type = 'businessentitypage';
UPDATE `vic_view` SET type = 'businesstemplate' WHERE type = 'businessentitypagepattern';
UPDATE `vic_view` SET type = 'articletemplate' WHERE type = 'businessentitypage' AND business_entity_id = 'article' ;
```

#UPGRADE 1.2.2

In your base.html.twig
- Change the ng-init of your <body> tag by using ng-init="init({% if view is defined %}'{{ view.cssHash }}'{% endif %})"
- Add {{ cms_page_css() }} Twig extension at the beginning of your <body> tag
