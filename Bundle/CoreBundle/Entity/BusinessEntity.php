<?php
namespace Victoire\Bundle\CoreBundle\Entity;

use Victoire\Bundle\CoreBundle\Entity\BusinessProperty;
/**
 *
 * @author Thomas Beaujean
 *
 */
class BusinessEntity
{
    protected $id = null;
    protected $class = null;
    protected $name = null;
    protected $businessProperties = array();

    /**
     * Get the id
     *
     * @return string The id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the name
     *
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the class
     *
     * @return string The class
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the class
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Add a business property
     *
     * @param BusinessProperty $businessProperty
     */
    public function addBusinessProperty(BusinessProperty $businessProperty)
    {
        //the type of business property (textable, slideable...)
        $type = $businessProperty->getType();

        if (!isset($this->businessProperties[$type])) {
            $this->businessProperties[$type] = array();
        }

        //add the business property indexed by the type
        $this->businessProperties[$type][] = $businessProperty;
    }

    /**
     * Get the business properties by type
     *
     * @param string $type
     * @return array The businnes properties
     */
    public function getBusinessPropertiesByType($type)
    {
        $bp = array();

        if (isset($this->businessProperties[$type])) {
            $bp = $this->businessProperties[$type];
        }

        return $bp;
    }

}