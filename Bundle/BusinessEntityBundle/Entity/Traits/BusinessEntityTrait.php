<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessEntity trait adds relationship with the entity proxies.
 */
trait BusinessEntityTrait
{
    /**
     * Association made dynamically in EntityProxySubscriber.
     */
    protected $proxy;

    /**
     * @var string
     *
     * @ORM\Column(name="visible_on_front", type="boolean")
     */
    private $visibleOnFront = true;

    /**
     * Set proxy.
     *
     * @param \Victoire\Bundle\CoreBundle\Entity\EntityProxy $proxy
     *
     * @return BusinessEntityTrait
     */
    public function setProxy(\Victoire\Bundle\CoreBundle\Entity\EntityProxy $proxy = null)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Get proxy.
     *
     * @return \Victoire\Bundle\CoreBundle\Entity\EntityProxy
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Is visibleOnFront.
     *
     * @return string
     */
    public function isVisibleOnFront()
    {
        return $this->visibleOnFront;
    }

    /**
     * Set visibleOnFront.
     *
     * @param string $visibleOnFront
     *
     * @return $this
     */
    public function setVisibleOnFront($visibleOnFront)
    {
        $this->visibleOnFront = $visibleOnFront;

        return $this;
    }

    /**
     * Get the content of an attribute of the current entity.
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getEntityAttributeValue($field)
    {
        if ($field) {
            $functionName = 'get'.ucfirst($field);

            $fieldValue = $this->{$functionName}();
        } else {
            $fieldValue = null;
        }

        return $fieldValue;
    }
}
