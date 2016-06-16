#UPGRADE 1.6

## v1.6.0

Add the new CriteriaBundle in your AppKernel: 
```php
    new Victoire\Bundle\CriteriaBundle\VictoireCriteriaBundle(),    
```
And new dependencies bundles: 
```php
    new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
    new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),    
```
To migrate from early 1.6 widget map and after widget migration command :

Disable Victoire\Bundle\CoreBundle\EventSubscriber\WidgetSubscriber before using this command.

```
php bin/console victoire:widget:migrate-owning-side
```

### FROM 1.5
A simple query can be use to migrate form 1.5 to 1.6
```sql
    INSERT INTO vic_view_translations (`translatable_id`, `name`, `slug`, `locale`)
    SELECT id, name, slug, locale FROM vic_view
```

### FROM 1.6 GEDMO/Translatable

To migrate from GEDMO with the new system you have to call this command:

```
php bin/console victoire:legacy:view_translation
```

It will populate the vic_view_translation table with all views name, slug and description.
It will populate the vic_article_translation table with all articles name, slug, description and images.
