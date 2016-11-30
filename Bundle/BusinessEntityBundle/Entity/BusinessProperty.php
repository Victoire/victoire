<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityBundle\Entity\BusinessPropertyRepository")
 * @ORM\Table("vic_business_property")
 */
class BusinessProperty
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
     * @ORM\Column(name="type", type="text", nullable=true)
     */
    protected $types = null;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name = null;

    /**
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity", inversedBy="businessProperties")
     */
    protected $businessEntity;

    /**
     * @var bool
     *
     * @ORM\Column(name="businessIdentifier", type="boolean")
     */
    protected $businessIdentifier;

    /**
     * Set the type.
     *
     * @param string $type
     */
    public function setTypes($types)
    {
        $data = @unserialize($types);
        if ($types === 'b:0;' || $data !== false) {
            $this->types = $types;
        } else {
            $this->types = serialize($types);
        }
    }

    /**
     * @return string the type of business property
     */
    public function getTypes()
    {
        return unserialize($this->types);
    }


    /**
     * Display object as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return BusinessEntity
     */
    public function getBusinessEntity()
    {
        return $this->businessEntity;
    }

    /**
     * @param BusinessEntity $businessEntity
     */
    public function setBusinessEntity(BusinessEntity $businessEntity)
    {
        $this->businessEntity = $businessEntity;
        $businessEntity->addBusinessProperty($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isBusinessIdentifier()
    {
        return $this->businessIdentifier;
    }

    /**
     * @param boolean $businessIdentifier
     */
    public function setBusinessIdentifier($businessIdentifier)
    {
        $this->businessIdentifier = $businessIdentifier;
    }


}
