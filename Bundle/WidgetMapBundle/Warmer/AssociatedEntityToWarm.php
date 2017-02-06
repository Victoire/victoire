<?php

namespace Victoire\Bundle\WidgetMapBundle\Warmer;

class AssociatedEntityToWarm
{
    const TYPE_MANY_TO_ONE = 'many_to_one';
    const TYPE_ONE_TO_MANY = 'one_to_many';

    protected $type;
    protected $inheritorEntity;
    protected $inheritorPropertyName;
    protected $entityId;
    protected $mappedBy;

    /**
     * Constructor.
     *
     * @param null $type
     * @param null $inheritorEntity
     * @param null $inheritorPropertyName
     * @param null $entityId
     * @param null $mappedBy              for OneToMany type only
     */
    public function __construct(
        $type = null,
        $inheritorEntity = null,
        $inheritorPropertyName = null,
        $entityId = null,
        $mappedBy = null)
    {
        $this->type = $type;
        $this->inheritorEntity = $inheritorEntity;
        $this->inheritorPropertyName = $inheritorPropertyName;
        $this->entityId = $entityId;
        $this->mappedBy = $mappedBy;
    }

    /**
     * @return null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param null $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInheritorEntity()
    {
        return $this->inheritorEntity;
    }

    /**
     * @param mixed $inheritorEntity
     *
     * @return $this
     */
    public function setInheritorEntity($inheritorEntity)
    {
        $this->inheritorEntity = $inheritorEntity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInheritorPropertyName()
    {
        return $this->inheritorPropertyName;
    }

    /**
     * @param mixed $inheritorPropertyName
     *
     * @return $this
     */
    public function setInheritorPropertyName($inheritorPropertyName)
    {
        $this->inheritorPropertyName = $inheritorPropertyName;

        return $this;
    }

    /**
     * Get entity id for ManyToOne type.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     *
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return null
     */
    public function getMappedBy()
    {
        return $this->mappedBy;
    }

    /**
     * @param null $mappedBy
     *
     * @return $this
     */
    public function setMappedBy($mappedBy)
    {
        $this->mappedBy = $mappedBy;

        return $this;
    }
}
