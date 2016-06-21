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
