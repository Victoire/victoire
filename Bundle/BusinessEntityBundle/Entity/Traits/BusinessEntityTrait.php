<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity\Traits;

use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

/**
 * BusinessEntity trait adds relationship with the entity proxies
 *
 */
trait BusinessEntityTrait
{
    /**
     * Association made dynamically in AnnotationDriver
     * @ORM\Column(name="proxy_id", type="integer")
     */
    protected $proxy;

    /**
     * @var string
     *
     * @ORM\Column(name="visible_on_front", type="boolean")
     */
    private $visibleOnFront = true;

    /**
     * Set proxy
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
     * Get proxy
     *
     * @return \Victoire\Bundle\CoreBundle\Entity\EntityProxy
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Add proxies
     *
     * @param \Victoire\Bundle\CoreBundle\Entity\EntityProxy $proxies
     *
     * @return BusinessEntityTrait
     */
    public function addProxie(\Victoire\Bundle\CoreBundle\Entity\EntityProxy $proxies)
    {
        $this->proxies[] = $proxies;

        return $this;
    }

    /**
     * Remove proxies
     *
     * @param \Victoire\Bundle\CoreBundle\Entity\EntityProxy $proxies
     */
    public function removeProxie(\Victoire\Bundle\CoreBundle\Entity\EntityProxy $proxies)
    {
        $this->proxies->removeElement($proxies);
    }

    /**
     * Is visibleOnFront
     *
     * @return string
     */
    public function isVisibleOnFront()
    {
        return $this->visibleOnFront;
    }

    /**
     * Set visibleOnFront
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
     * Get the content of an attribute of the current entity
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

     /**
     * Set proxy
     *
     * @param EntityProxy $proxy
     *
     * @return PostPage
     */
    public function setProxy(EntityProxy $proxy = null)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Get proxy
     *
     * @return EntityProxy
     */
    public function getProxy()
    {
        return $this->proxy;
    }
}
