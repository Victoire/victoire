#UPGRADE 2.0

##UPGRADE 2.0.0

The 2.0 version cleans the widget edit views.
To upgrade to this version, you have to refactor your widgets "edit" and "new" templates to use the "form_static" and "form_entity" blocks.
You don't have to redefine the "picker" div because it's been put outside the block in the parent template.

## UPGRADE 2.2.0
You need to execute the following SQL command to upgrade your database.
```sql
UPDATE vic_link SET target = '_modal' WHERE target = 'ajax-modal';
```

## UPGRADE 2.2.18
You need to execute the following SQL command to upgrade your database.
It simply remove deprecated associations that are not required anymore and could generate errors in WidgetDataWarmer.
```sql
UPDATE vic_widget_map SET widget_id = NULL;
UPDATE vic_widget SET view_id = NULL;
```

## UPGRADE 2.2.31
If you use custom Filter in your project, you need to fix service declaration
```yaml
# short
my_custom_filters.form.type:
    class: AppBundle\Filter\MyCustomFilter
    parent: victoire_filter_bundle.abstract_base_filter
    tags:
        - { name: form.type }
        - { name: victoire_core.filter }

# full
my_custom_filters.form.type:
    class: AppBundle\Filter\MyCustomFilter
    arguments:
        - "@doctrine.orm.entity_manager"
        - "@request_stack"
        - "@victoire_filter_bundle.filter_form_field_query.handler"
    tags:
        - { name: form.type }
        - { name: victoire_core.filter }
```

If you use custom filter with more constructor's arguments, you need to replace Request with RequestStack too 
