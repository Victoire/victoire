<?php
namespace Victoire\Bundle\CoreBundle\Entity;


class BusinessEntity
{
    protected $id = null;
    protected $class = null;
    protected $name = null;


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
}