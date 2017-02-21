#UPGRADE
When upgrading Victoire, please execute the migrations provided.
You need to enable DoctrineMigrationBundle in your project according to [this doc](http://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html)

Then, execute the migration command with the custom configuration:
`php bin/console doctrine:migrations:migrate --configuration=vendor/victoire/victoire/migration.yml`

To avoid to do this manually, you can add this command in your composer.json:

```
"scripts": {
    "post-update-cmd": [
        "php bin/console doctrine:migrations:migrate --configuration=vendor/victoire/victoire/migration.yml -n"
    ]
},
```
