Victoire CMS Force Bundle
============

Need to add Force in a Victoire CMS website?

First you need to have a valid Symfony2 Victoire edition.
Then you just have to run the following composer command:

    php composer.phar require victoire/force-widget

Do not forget to add the bundle in your AppKernel!

```php
    class AppKernel extends Kernel
    {
	public function registerBundles()
	{
	    $bundles = array(
		...
		new Victoire\Widget\ForceBundle\VictoireWidgetForceBundle(),
	    );

	    return $bundles;
	}
    }
```
