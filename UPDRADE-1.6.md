#UPGRADE 1.6

## v1.6.0

Views are now translated. To migrate an existing database with the new system you have to call this command:

```
php bin/console victoire:legacy:view_translation
```

It will populate the vic_view_translation table with all views name, slug and description.
It will populate the vic_article_translation table with all articles name, slug, description and images.
