# WidgetMap

##What is WidgetMaps

A WidgetMap lists and organizes the widgets within a view. It's a slot table in which we find a items list and define their positions.



##WidgetMap

The WidgetMap is used to positionnate a widget in a slot.
Properties:

Property           | Description
----------------   | -------------
widget             | ref to Widget object
view               | ref to View object
position           | is the widget before or after it's parent
action             | could be create, overwrite or delete
parent             | contains the WidgetMap under which the WidgetMap will be placed before or after
replaced           | In *overwrite* mode, it is used to recover the overwriten WidgetMap and display the new one instead

#WidgetMapBuilder

The WidgetMapBuilder stands forbuild a WidgetMap for a given view. The build method will build the entire widgetMap taking into consideration the page hierarchy. This widgetMap would be directly get from the getWidgetMap($built = true) method from the view object.

##Builder (victoire_widget_map.builder)

Fonction        | Description
---------------- | -------------  
@build | build complete widgetMap from View

Helper: (victoire_widget_map.helper)

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
@updateWidgetMapOrder    | called by widgetController->updatePosition: update widgetMap position

##Actions

CREATE | OVERWRITE | DELETE
---------------- | ---------------- | -------------
This action is used to define a widget when it has been created in the current view. | Used to define that the widget is an overwrite instance of an extended widget owned by a template | Used to hide an extended widget owned by a template

##Position Reference

The positionReference exists to be able to position the widgets within a view while dealing with the ones of parents templates.


