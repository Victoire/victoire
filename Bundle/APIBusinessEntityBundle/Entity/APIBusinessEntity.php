<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * The ORM business Entity.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntityRepository")
 */
class APIBusinessEntity extends BusinessEntity
{
    const TYPE = 'api';

    /**
     * @var string
     *
     * @ORM\Column(name="resource", type="string", length=255)
     */
    protected $resource;

    /**
     * @var string
     *
     * @ORM\Column(name="getMethod", type="text")
     */
    protected $getMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="listMethod", type="text")
     */
    protected $listMethod;

    /**
     * @var APIEndpoint
     *
     * @ORM\ManyToOne(targetEntity="APIEndpoint")
     * @ORM\JoinColumn(name="endpoint_id", referencedColumnName="id", onDelete="SET NULL"))
     */
    protected $endpoint;

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return APIEndpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param APIEndpoint $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @return string
     */
    public function getGetMethod()
    {
        return $this->getMethod;
    }

    /**
     * @param string $getMethod
     */
    public function setGetMethod($getMethod)
    {
        $this->getMethod = $getMethod;
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

}
