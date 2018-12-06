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
     * @ORM\OneToMany(targetEntity="Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty", mappedBy="businessEntity", cascade={"persist", "remove"})
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
     * @return int The id
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
     * @return BusinessProperty[] The business properties
     */
    public function getBusinessProperties()
    {
        return $this->businessProperties;
    }

    /**
     * Get a business property by name.
     *
     * @return BusinessProperty|null The business property
     */
    public function getBusinessPropertyByName($name)
    {
        foreach ($this->businessProperties as $businessProperty) {
            if ($businessProperty->getName() == $name) {
                return $businessProperty;
            }
        }
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
     * @return ArrayCollection The businnes properties
     */
    public function getBusinessPropertiesByType($type)
    {
        if (!is_array($type)) {
            $type = [$type];
        }
        $bp = new ArrayCollection();
        foreach ($this->getBusinessProperties() as $property) {
            if (count(array_diff($type, $property->getTypes())) == 0) {
                $bp->add($property);
            }
        }

        return $bp;
    }

    /**
     * Get the business properties names by type.
     *
     *
     * @return ArrayCollection The businnes properties
     */
    public function getBusinessParameters()
    {
        $bp = new ArrayCollection();
        /** @var BusinessProperty $property */
        foreach ($this->getBusinessProperties() as $property) {
            if ($property->hasType('businessParameter')) {
                $bp->add($property);
            }
        }

        return $bp;
    }

    /**
     * Get the business identifiers.
     *
     *
     * @return ArrayCollection The businnes properties
     */
    public function getBusinessIdentifiers()
    {
        $bp = new ArrayCollection();
        /** @var BusinessProperty $property */
        foreach ($this->getBusinessProperties() as $property) {
            if ($property->hasType('businessIdentifier')) {
                $bp->add($property);
            }
        }
        if ($bp->count() < 1) {
            throw new \Exception(sprintf('The businessEntity %s must have at lease one businessIdentifier property', $this->name));
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
