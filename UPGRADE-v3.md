#UPGRADE 3.0

The 3.0 release brings API support for BusinessEntities.
The BusinessEntity and BusinessProperty annotations does not work anymore,
so you have to execute the doctrine migrations to use the new way described
in the [BusinessEntityBundle readme](https://github.com/victoire/victoire/blob/3.0/Bundle/BusinessEntityBundle/README.md)

`php bin/console doctrine:migrations:migrate --configuration=vendor/victoire/victoire/migration.yml`
