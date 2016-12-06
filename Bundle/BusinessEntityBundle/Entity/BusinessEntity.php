<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * The business Entity.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntityRepository")
 * @ORM\Table("vic_business_entity")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
abstract class BusinessEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var BusinessProperty[]
     *
     * @ORM\OneToMany(targetEntity="Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty", mappedBy="businessEntity")
     */
    protected $businessProperties;

    /**
     * @var array
     *
     * @ORM\Column(name="availableWidgets", type="text")
     */
    protected $availableWidgets;

    protected $disable = false;

    public function __construct()
    {
        $this->businessProperties = new ArrayCollection();
    }

    /**
     * Get the id.
     *
     * @return string The id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the name.
     *
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Add a business property.
     *
     * @param BusinessProperty $businessProperty
     */
    public function addBusinessProperty(BusinessProperty $businessProperty)
    {
        $this->businessProperties->add($businessProperty);
    }

    /**
     * Get the business properties.
     *
     * @return array The business properties
     */
    public function getBusinessProperties()
    {
        return $this->businessProperties;
    }

    /**
     * Get the business properties.
     *
     * @return array The business properties
     */
    public function setBusinessProperties($businessProperties)
    {
        return $this->businessProperties = $businessProperties;
    }

    /**
     * Get the business properties by type.
     *
     * @param string $type
     *
     * @return array The businnes properties
     */
    public function getBusinessPropertiesByType($type)
    {
        $bp = [];
        foreach ($this->getBusinessProperties() as $property) {
            if (in_array($type, $property->getTypes())) {
                $bp[] = $property;
            }
        }

        return $bp;
    }

    /**
     * @return array
     */
    public function getAvailableWidgets()
    {
        return unserialize($this->availableWidgets);
    }

    /**
     * @param array $availableWidgets
     */
    public function setAvailableWidgets($availableWidgets)
    {
        $this->availableWidgets = serialize($availableWidgets);
    }

    /**
     * @return bool
     */
    public function isDisable()
    {
        return $this->disable;
    }

    /**
     * @param bool $disable
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
    }

    public function __toString()
    {
        return $this->name;
    }
}
