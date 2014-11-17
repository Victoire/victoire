# WidgetMap

##Qu'est ce qu'une WidgetMap

Une WidgetMap permet de répertorier, cartographier les widgets présents au sein d'une vue. Elle a la forme d'un tableau de slots dans lequel on retrouve une liste d'élement positionnant un contenu dans ce slot.

##Slot

Un slot est un conteneur de widgets.
C'est un objet non persisté qui est créé dynamiquement par le WidgetMapBuilder.

##WidgetMapItem

Élement d'une widgetMap, le WidgetMapItem sert à positionner un contenu dans un slot.
Il a les caractéristiques suivantes :

Proprieté        | Description
--               |  --
widgetId         | ref to Widget object
position         | the order to display widgets
action           | could be create or overwrite
positionReferece | contains the id of the overwriten widget in order to order the child widget according to it's old position
replacedWidgetId | In *overwrite* mode, it is used to recover the overwriten widget and display the new one instead

#WidgetMapBuilder

La classe WidgetMapBuilder est responsable de construire une WidgetMap pour une vue donnée. La méthode build sera chargée de construire la widgetMap complète en prenant en compte la hiérarchie de la page. Cette widgetMap pour être récupérée directement depuis l'objet View avec la méthode getWidgetMap($built = true).

Builder (victoire_widget_map.builder)

Fonction        | Description
--              |  --        
@build | build complete widgetMap from View

Helper : (victoire_widget_map.helper)

Fonction        | Description
--               |  --       
@getNextAvailablePosition | Used to know the very next available position for a given

WidgetMapToArrayTransformer (victoire_widget_map.transformer)

Fonction          | Description
--                |  --        
@transform        | WM to array
 @reverseTransform | array to WM

Manager (victoire_widget_map.manager)

Fonction                 | Description
--                       | --        
@overwriteWidgetMap      | --
@deleteWidgetFromView    | --
@updateWidgetMapFromView | --
@updateWidgetMapOrder    | appelée par le widgetController->updatePosition : met à jour les positions du widgetMap

##Actions

CREATE|OVERWRITE|DELETE
--    | --      |--    
Cette action est utilisée pour définir un widget comme ayant été créé dans la vue courante.    |Utilisé pour définir que le widget est une surcharge d'un widget hérité depuis le modèle|Utilisé pour cacher un widget appartenant au modèle de page

##Position Reference

La positionReference est une notion apparue pour réussir à positionner les widgets d'une vue en composant avec l'ordre de la vue et ceux des modèles parents.


