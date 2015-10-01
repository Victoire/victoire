<?php

namespace Victoire\Bundle\MediaBundle\Helper;

use Doctrine\ORM\Proxy\Proxy;

/**
 * Helper for looking up the classname, not the ORM proxy.
 */
class ClassLookup
{
    /**
     * Get full class name of object (ie. class name including full namespace).
     *
     * @param mixed $object
     *
     * @return string the name of the class and if the given $object isn't a vaid Object false will be returned.
     */
    public static function getClass($object)
    {
        return ($object instanceof Proxy) ? get_parent_class($object) : get_class($object);
    }

    /**
     * Get class name of object (ie. class name without namespace).
     *
     * @param mixed $object
     *
     * @return string
     */
    public static function getClassName($object)
    {
        $className = explode('\\', self::getClass($object));

        return array_pop($className);
    }
}
