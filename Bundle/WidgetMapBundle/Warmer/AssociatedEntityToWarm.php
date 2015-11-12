<?php

namespace Victoire\Bundle\WidgetMapBundle\Warmer;

class AssociatedEntityToWarm
{
    protected $inheritorEntity;
    protected $inheritorPropertyName;
    protected $entityId;

    /**
     * Constructor.
     *
     * @param null $inheritorEntity
     * @param null $inheritorPropertyName
     * @param null $entityId              for ManyToOne type
     */
    public function __construct($inheritorEntity = null, $inheritorPropertyName = null, $entityId = null)
    {
        $this->inheritorEntity = $inheritorEntity;
        $this->inheritorPropertyName = $inheritorPropertyName;
        $this->entityId = $entityId;
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
}
