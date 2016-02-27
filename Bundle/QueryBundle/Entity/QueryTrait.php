<?php

namespace Victoire\Bundle\QueryBundle\Entity;

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
     *  Auto list mode: businessentity type.
     *
     * @var string
     * @ORM\Column(name="business_entity_id", type="string", nullable=true)
     */
    protected $businessEntityId;

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
     * Get businessEntityId.
     *
     * @return int
     */
    public function getBusinessEntityId()
    {
        return $this->businessEntityId;
    }

    /**
     * Set businessEntityId.
     *
     * @param string $businessEntityId The business entity name
     */
    public function setBusinessEntityId($businessEntityId)
    {
        $this->businessEntityId = $businessEntityId;
    }
}
