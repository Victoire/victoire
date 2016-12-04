# ViewReferenceBundle

##What is a ViewReference

A ViewReference is a structural element to define the website's pages.

##Why?

Several kind of pages exists in Victoire:

- Page (any simple page)
- Blog (which is very close from Page)
- BusinessPage (which is a BusinessEntity view based on a BusinessTemplate)
- VirtualBusinessPage (same as BusinessPage but computed, not yet persisted)

##What is used for?

Victoire brings several kinds of pages, mostly persisted and for BusinessPage generated from a BusinessTemplate.
So we need to save somewhere our page list to be able to retrieve them easily: we choose to write an XML file in the cache named `viewsReference.xml`.
It's a viewsReference tree, easy to parse which contains some critical information about the view.

##What does it looks like?

It's the ViewReference object

```php
    $id;
    $locale;
    $slug;
    $name;
    $viewId;
    $viewNamespace;
    $entityNamespace;
    $entityId;
    $templateId;
```

##Lifecycle

Every ViewReference is built by a ViewReferenceBuilder in 2 times:

- when running the victoire:viewReference:generate
- on doctrine ORM `persist` / `update` / `remove` events catched by `BusinessEntitySubscriber` and `ViewReferenceSubscriber`

##Who's in charge?

To deal with the viewsReferences, your best friends will be:

- ViewHelper
    - buildViewsReferences
    - buildViewReferenceRecursively
- ViewReferenceHelper
    - ::generateViewReferenceId
    - buildViewReferenceRecursively
- ViewReferenceProvider
    - getReferencableViews
- ViewReferenceRedisDriver
    - findReferenceByView
    - saveReferences
    - saveReference
    - removeReference
    - getReferenceByUrl
    - getReferencesByParameters
    - getOneReferenceByParameters
    - hasReference
    - getChoices
- ViewReferenceListener
    - updateViewReference
    - removeViewReference

##How they are persisted?

Check the Redis documentation [here](Resources/doc/redis.md)
