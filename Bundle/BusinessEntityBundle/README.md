#BusinessEntityBundle

##Overview

Business entities are the foundations of any application built with VictoireDCMS.
They stand for the customer's business logic, they are ubiquitous and their deep integration is the major asset of VictoireDCMS.
Widgets and business entities are closely related because the widgets are thought to be able to render static data as well as business entity's data.

##How do I turn an Entity into a BusinessEntity?

To declare an Entity as a BusinessEntity, you'll have to consider several things:

- The BusinessEntityTrait
- The `BusinessEntity` annotation
- The `BusinessProperty` annotations (according to the the enabled widgets `ReceiverProperty` annotations)


###Use the BusinessEntityTrait

```
...
class Product
{
    use Victoire\Bundle\BusinessEntityBundle\Entity\Traits\BusinessEntityTrait;
    ...
```


###@VIC/BusinessEntity( {{ availableWidgets }} )

Add this annotation to the Entity you want to render through widgets.
It's a Class annotation so you need to add the following line just before the class declaration and you'll have to pass the list of widgets you want to enable to render your BusinessEntity as parameter. For example, if we have a BusinessEntity called `Product` with the property `title` and we want to be able to render it with the widgets `Text` and `Title`

```
@VIC\BusinessEntity({"Title", "Text"}))
```

You can add any enabled widget but you'll have to check the compatibility by looking the `@VIC/ReceiverProperty` annotation in the Widget (eg. If the widget has a ReceiverProperty named` **`textable`** `, you'll have to declare also at least one ` **`textable`** `BusinessProperty).`

###@VIC/BusinessProperty( {{ renderableTypes }} ) and @VIC/ReceiverProperty(renderableType)

The BusinessProperty annotation is used to make available some property (column or not !) for the enabled widgets (by `@VIC/BusinessEntity` annotation). To understand this concept, you have to also take a look at the `@VIC/ReceiverProperty` annotation because they are working hand in hand, like a key and a lock, the widget define the lock and the BusinessProperties are some possible keys.

See the BusinessEntity side (the `key` side, to keep the metaphor)

```
<?php

namespace Acme\DemoBundle\Entity;

    ...
    
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @VIC\BusinessProperty({"textable"})
     */
    private $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     * @VIC\BusinessProperty({"textable"})
     */
    private $description;
```

See the Widget side (the `lock`, to keep the metaphor)

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


###Full BusinessEntity example

```
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\BusinessEntityBundle\Entity\Traits\BusinessEntityTrait;

/**
 * Product Entity class
 *
 * @VIC\BusinessEntity({"Listing", "Title", "Text", "Image", "Cover", "Button", "CKEditor", "Slider"})
 * @ORM\Entity(repositoryClass="Front\AppBundle\Repository\ReferenceRepository")
 */
class Product
{
    use BusinessEntityTrait;
    
    ...

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @VIC\BusinessProperty({"textable", "businessParameter"})
     */
    private $title;
    
    ...

}
```


##How does the Annotation System work?

After you declare your BusinessEntities, you have to warmup the cache because the `EntityProxy` (*technical link between BusinessEntities and views and widgets*) need to be refresh.

First, the `VictoireCoreBundle::boot()` method add the `EntityProxyCacheDriver` to the `DoctrineDriverChain` because the system will generate the `EntityProxy` Entity file in the victoire cache directory. 

The `BusinessEntityWarmer` is called, it asks the `AnnotationDriver` to get all the entity class names to parse them (*if you have some BusinessEntity outside the `src` directory, you'll need to add your path to the `victoire_core.base_paths` config*) and extract the potential `@VIC` annotations.

The `CacheSubscriber` receives two kind of events on `victoire.business_entity_annotation_load` and `victoire.widget_annotation_load` and will respectively call the `addBusinessEntityInfo` and `addWidgetInfo` functions which will deal with these informations and tell the `CacheBuilder` to update the `VictoireCache` (which extends `PhpFileCache`) and will write a file in the victoire cache directory.

The `EntityProxyWarmer` will be called just after to generate the `EntityProxy.php` file thanks to the `EntityProxyGenerator` and the `VictoireCache` we've built just before.

Then our job is done and we can let Doctrine drivers (and our `EntityProxyDriver` injected in first step) do their job and load every class Metadatas.

As a picture is worth a thousand words…

![Alt text](http://appventus.com/uploads/media/55cde79fc1fff.jpeg?raw=true "Global View")



![Alt text](http://appventus.com/uploads/media/55cde7adbd2c7.jpeg?raw=true "Large View")



![Alt text](http://appventus.com/uploads/media/55cde7c355851.jpeg?raw=true "Details")
