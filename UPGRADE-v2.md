#UPGRADE 2.0

##UPGRADE 2.0.0

The 2.0 version cleans the widget edit views.
To upgrate to this version, you have to refactor your widgets "edit" and "new" templates to use the "form_static" and "form_entity" blocks.
You don't have to redefine the "picker" div because it's been put outside the block in the parent template.

