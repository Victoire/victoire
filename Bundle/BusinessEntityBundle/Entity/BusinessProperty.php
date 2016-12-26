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
     * @var array
     *
     * @ORM\Column(name="choices", type="text", nullable=true)
     */
    protected $choices;

    /**
     * @var string
     *
     * @ORM\Column(name="listMethod", type="text", nullable=true)
     */
    protected $listMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="filterMethod", type="text", nullable=true)
     */
    protected $filterMethod;

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
        if (!$this->types) {
            return [];
        }

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
     * @return bool
     */
    public function isBusinessIdentifier()
    {
        return $this->businessIdentifier;
    }

    /**
     * @param bool $businessIdentifier
     */
    public function setBusinessIdentifier($businessIdentifier)
    {
        $this->businessIdentifier = $businessIdentifier;
    }

    /**
     * Set the choices.
     *
     * @param string $choices
     */
    public function setChoices($choices)
    {
        $data = @unserialize($choices);
        if ($choices === 'b:0;' || $data !== false) {
            $this->choices = $choices;
        } else {
            $this->choices = serialize($choices);
        }
    }

    /**
     * @return string the choice of business property
     */
    public function getChoices()
    {
        if (!$this->choices) {
            return [];
        }

        return unserialize($this->choices);
    }

    /**
     * @return string
     */
    public function getListMethod()
    {
        return $this->listMethod;
    }

    /**
     * @param string $listMethod
     */
    public function setListMethod($listMethod)
    {
        $this->listMethod = $listMethod;
    }

    /**
     * @return string
     */
    public function getFilterMethod()
    {
        return $this->filterMethod;
    }

    /**
     * @param string $filterMethod
     */
    public function setFilterMethod($filterMethod)
    {
        $this->filterMethod = $filterMethod;
    }

}
