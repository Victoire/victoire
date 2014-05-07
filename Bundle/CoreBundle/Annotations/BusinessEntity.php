<?php
namespace Victoire\Bundle\CoreBundle\Annotations;

/**
 * BusinessEntity mark an entity as sementical
 *
 * @Annotation
 **/
class BusinessEntity
{
    private $widgets;

    /**
     * define supported widgets
     *
     * @param array $widgets supported widgets
     *
     **/
    public function __construct($widgets = null)
    {
        $this->widgets = $widgets;
    }


    /**
     * Get widgets associated to this businessEntity
     * @return null|array
     */
    public function getWidgets()
    {
        if ($this->widgets === null || !array_key_exists('value', $this->widgets)) {
            return null;
        }

        //return an array, no matter one or many widget defined
        if (count($this->widgets['value']) > 1) {
            return $this->widgets['value'];
        } else {
            return array($this->widgets['value']);
        }
    }
}
