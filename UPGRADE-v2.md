#UPGRADE 2.0

##UPGRADE 2.0.0

The 2.0 version cleans the widget edit views.
To upgrade to this version, you have to refactor your widgets "edit" and "new" templates to use the "form_static" and "form_entity" blocks.
You don't have to redefine the "picker" div because it's been put outside the block in the parent template.

## UPGRADE 2.2.0
You need to execute the following SQL command to upgrade your database
UPDATE vic_link SET target = '_modal' WHERE target = 'ajax-modal'

## UPGRADE 2.2.16
In order to reduce the number of Doctrine queries by using WidgetDataWarmer, you can execute the following SQL commands.
UPDATE vic_widget_map SET widget_id = NULL;
UPDATE vic_widget SET view_id = NULL;
It simply remove deprecated associations that are not required anymore.