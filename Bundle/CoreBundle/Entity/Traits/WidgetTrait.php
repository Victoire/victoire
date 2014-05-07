<?php
namespace Victoire\Bundle\CoreBundle\Entity\Traits;

/**
 * This trait add magic methods to return
 * field value or linked entity value
 *
 * if $name is defined in our widget, and widget is in auto mode (businessEntityName is set)
 * call the magic __get method to return the entity field value
 * else, just call the classic getter
 *
 **/
trait WidgetTrait
{

    /**
     * if __isset returns true, returns linked entity value
     * else, call default get() method
     *
     * @param string $name magic called value
     * @return liked entity value
     **/
    public function __get($name)
    {
        if ($this->getEntity()) {
            return $this->getEntity()->getReferedValue($this->getBusinessEntityName(), $name);
        }

    }

    /**
     * check if asked field is defined in the entity
     * and if entity is in proxy mode
     *
     * @param string $name magic called value
     * @return liked entity value
     **/
    public function __isset($name)
    {
        if (array_key_exists($name, get_class_vars(get_class($this)))) {
            if ($this->getBusinessEntityName()) {
                return true;
            }
        }

        return false;
    }
}
