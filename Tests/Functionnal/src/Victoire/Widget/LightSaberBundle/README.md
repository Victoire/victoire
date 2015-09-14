Victoire CMS LightSaber Bundle
============

Need to add a lightsaber in a victoire cms website ?

First you need to have a valid Symfony2 Victoire edition.
Then you just have to run the following composer command :

    php composer.phar require victoire/lightsaber-widget

Do not forget to add the bundle in your AppKernel !

```php
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                ...
                new Victoire\Widget\LightSaberBundle\VictoireWidgetLightSaberBundle(),
            );

            return $bundles;
        }
    }
```
