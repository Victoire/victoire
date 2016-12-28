<?php

namespace Victoire\Bundle\QueryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * Query trait adds the query fields.
 */
trait QueryTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text", nullable=true)
     */
    protected $query;

    /**
     * @var string
     *
     * @ORM\Column(name="orderBy", type="text", nullable=true)
     */
    protected $orderBy;

    /**
     * @deprecated
     *  Auto list mode: businessentity type.
     *
     * @var string
     * @ORM\Column(name="business_entity_name", type="string", nullable=true)
     */
    protected $businessEntityName;
    /**
     *
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity")
     * @ORM\JoinColumn(name="business_entity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $businessEntity;

    /**
     * Get query.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set query.
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Get orderBy.
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Set orderBy.
     *
     * @param string $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @deprecated
     * Get businessEntityName.
     *
     * @return int
     */
    public function getBusinessEntityName()
    {
        return $this->getBusinessEntity()->getName();
    }

    /**
     * @return mixed
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
    }

}
