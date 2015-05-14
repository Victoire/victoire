# WidgetMap

##What is a WidgetMap

A WidgetMap list and organize the widgets within a view. It's a slot table in which we find a items list and define their positions.

##Slot

A slot is widget container.
It's a non-persisted object dynamically created by WidgetMapBuilder.

##WidgetMapItem

The WidgetMapItem is used to positionnate a widget in a slot.
Properties :

Property         | Description
---------------- | -------------
widgetId         | ref to Widget object
position         | the order to display widgets
action           | could be create or overwrite
positionReferece | contains the id of the overwriten widget in order to order the child widget according to it's old position
replacedWidgetId | In *overwrite* mode, it is used to recover the overwriten widget and display the new one instead

#WidgetMapBuilder

The WidgetMapBuilder stands forbuild a WidgetMap for a given view. The build method will build the entire widgetMap taking into consideration the page hierarchy. This widgetMap would be directly get from the getWidgetMap($built = true) method from the view object.

##Builder (victoire_widget_map.builder)

Fonction        | Description
---------------- | -------------  
@build | build complete widgetMap from View

Helper : (victoire_widget_map.helper)

Fonction        | Description
---------------- | -------------
@getNextAvailablePosition | Used to know the very next available position for a given

WidgetMapToArrayTransformer (victoire_widget_map.transformer)

Fonction          | Description
---------------- | -------------
@transform        | WM to array
 @reverseTransform | array to WM

Manager (victoire_widget_map.manager)

Fonction                 | Description
---------------- | -------------
@overwriteWidgetMap      | --
@deleteWidgetFromView    | --
@updateWidgetMapFromView | --
@updateWidgetMapOrder    | called by widgetController->updatePosition : update widgetMap position

##Actions

CREATE | OVERWRITE | DELETE
---------------- | ---------------- | -------------
This action is used to define a widget when it has been created in the current view. | Used to define that the widget is an overwrite instance of an extended widget owned by a template | Used to hide an extended widget owned by a template

##Position Reference

The positionReference exists to be able to position the widgets within a view while dealing with the ones of parents templates.


