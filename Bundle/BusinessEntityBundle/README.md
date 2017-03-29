#BusinessEntityBundle

##Overview

Business entities are the foundations of any application built with VictoireDCMS.
They stand for the customer's business logic, they are ubiquitous and their deep integration is the major asset of VictoireDCMS.
Widgets and business entities are closely related because the widgets are thought to be able to render static data as well as business entity's data.

##How do I turn an Entity into a BusinessEntity?

To declare an Entity as a BusinessEntity, you'll have to consider several things:

- The BusinessEntityTrait
- The `BusinessEntity` entity
- The `BusinessProperty` entities (according to the the enabled widgets `ReceiverProperty` annotations)


###Use the BusinessEntityTrait

```
...
class Product
{
    use Victoire\Bundle\BusinessEntityBundle\Entity\Traits\BusinessEntityTrait;
    ...
```


###BusinessEntity
To enable a Doctrine Entity as a BusinessEntity, you have to add an entry in the vic_business_entity table.
The field 'name' is used to identify the BusinessEntity, generaly you can use the entity name
The field 'class' is the complete namespace of the Doctrine entity
The field 'type' is used to discriminate the ORN BusinessEntities from the API ones. Use 'ormbusinessentity' as value.
The field 'availableWidgets' is a seralized array that list the widgets enabled for the BusinessEntity.

You can add any enabled widget but you'll have to check the compatibility by looking the `@VIC/ReceiverProperty` annotation in the Widget (eg. If the widget has a ReceiverProperty named` **`textable`** `, you'll have to declare also at least one ` **`textable`** `BusinessProperty).`



###BusinessProperty and @VIC/ReceiverProperty(renderableType)

The BusinessProperty is used to make available some property (column or not !) for the enabled widgets. To understand this concept, you have to also take a look at the `@VIC/ReceiverProperty` annotation because they are working hand in hand, like a key and a lock, the widget define the lock and the BusinessProperties are some possible keys.



```
<?php

namespace Victoire\Widget\TitleBundle\Entity;

    ...

    /**
     * @var text
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     * @VIC\ReceiverProperty("textable")
     */
    private $content;
```

This annotation makes the widget's $content property available to render 'textable' properties.
To define a 'textable' property, you have to add an entry in the vic_business_property
The field 'business_entity_id' links the property to a BusinessEntity
The field 'name' must reflect the property name of the entity
The field 'types' is a serialized array with a type od data, it could be 'textable', 'dateable', 'imageable' or any other format you need.



##How does the Annotation System work?

When you warmup the cache, the `BusinessEntityWarmer` is called, it asks the `AnnotationDriver` to get all the widgets entity class names to parse them and extract the potential `@VIC\BusinessProperty` annotations.

The `CacheSubscriber` receives the event `victoire.widget_annotation_load` and will call the `Properties` functions which will deal with these informations and tell the `CacheBuilder` to update the `VictoireCache` (which extends `PhpFileCache`) and will write a file in the victoire cache directory.

