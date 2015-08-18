#Manage Views

##Chain

A chain list all views managers and allow to find.

##Managers

###ViewManagerInterface

This interface describe all methods that we need for a view.
For the moment we just a method to build the correct ViewReference associated.

###BaseViewManager

This Class have the setter for basic views Managers service dependence.

###ViewsManager

Managers must manage all events associated to a view.
Extends baseViewManager and implements viewManagerInterface

###config

To add a view Manager you must specify the tag for the compiler and the view namespace.

        my_bundle.manager.my_entity_view_manager:
            class: Path\to\MyEntityViewManager
            parent: victoire_core.manager.base_view_manager
            tags:
                - { name: victoire_core.view_manager, view: Path\To\MyViewEntity }
