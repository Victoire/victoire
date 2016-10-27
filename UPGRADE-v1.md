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

#UPGRADE 1.4

## v1.4.0

There were a large refactor of WidgetMap system from 1.3.

- Run the following command to migrate old system into new one :

```
php bin/console victoire:widget-map:migrate
```

#UPGRADE 1.6

## v1.6.0

- Register the following Bundles:

```php
new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
new Victoire\Bundle\CriteriaBundle\VictoireCriteriaBundle(),
```

- Views are now translated with Knp\DoctrineBehaviorsBundle.

First, do some manual steps:

- drop the `vic_view_i18n` table manually
- run the following sql command:

```sql
CREATE TABLE `vic_view_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translatable_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vic_view_translations_unique_translation` (`translatable_id`,`locale`),
  KEY `IDX_A8FED5872C2AC5D3` (`translatable_id`),
  CONSTRAINT `FK_A8FED5872C2AC5D3` FOREIGN KEY (`translatable_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `vic_view_translations` (`translatable_id`, `name`, `slug`, `locale`) SELECT id, name, slug, locale FROM `vic_view`;
INSERT INTO `vic_article_translations` (`translatable_id`, `image_id`, `name`, `description`, `slug`, `locale`) SELECT id, image_id, name, description, slug, 'fr' FROM `vic_article`;
```

Then update the schema and run the migration command:
```
php bin/console doc:schema:update --force --dump-sql
php bin/console victoire:widget:migrate-owning-side
```

- The layout system changed. Now, you need to define layouts in `VictoireCoreBundle`, so move your Layout directory into.
- The vic_link has also been impacted so you'll have to migrate the links as follow:

`If the website is only in french`:
```sql
UPDATE vic_link SET locale = 'fr', view_reference = CONCAT(view_reference, "_fr") WHERE link_type = 'viewReference';
```

#UPGRADE 1.7

## 1.7.0

Layouts architecture were a piece of shit... this version cleans up but also breaks things and you need to follow these steps to fix your project:

- don't override `VictoireCoreBundle:Layout:layout.html.twig` directly but `VictoireCoreBundle:Layout:defaultLayout.html.twig`
- the initial `VictoireCoreBundle:Layout:fullWidth.html.twig` layout is now known as `VictoireCoreBundle:Layout:defaultLayout.html.twig`
- the `VictoireCoreBundle:Layout:frontLayout.html.twig` does not exist anymore
- the `VictoireCoreBundle:Layout:base.html.twig`'s blocks were removed:
    - foot_script_additional
    - javascript
- the `VictoireCoreBundle:Layout:base.html.twig` has some new blocks:
    - victoire_ui
    - body_header
    - body_content
    - body_footer
- the `VictoireCoreBundle:Layout:layout.html.twig`:
    - has a new `body_content_main` block
    - wrap the `body_content_main` in a main#content tag
    - declare the **main_content** `cms_slot_widgets` (in the `body_content_main` block)
- the `fos_js_routes.js` is now generated with the `prod` suffix in `prod` environment

## 1.7.7
- The "getMainCurrentView" method disapeard from CurrentViewHelper, you have to use "getCurrentView" instead, which have the exact same behavior.
