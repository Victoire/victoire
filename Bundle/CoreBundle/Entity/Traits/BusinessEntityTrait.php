<?php
namespace Victoire\Bundle\CoreBundle\Entity\Traits;

/**
 * BusinessEntity trait adds relationship with the entity proxies
 *
 */
trait BusinessEntityTrait
{

    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy")
     */
    protected $proxy;

    /**
     * proxies relation is inejcted by Victoire\Bundle\CoreBundle\EventSubscriber\EntityProxySubscriber
     * like : OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy", mappedBy="{{businessEntityName}}")
     */
    protected $proxies;

    /**
     * Set proxy
     *
     * @param \Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy $proxy
     *
     * @return PostPage
     */
    public function setProxy(\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy $proxy = null)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Get proxy
     *
     * @return \Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Add proxies
     *
     * @param \Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy $proxies
     *
     * @return PostPage
     */
    public function addProxie(\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy $proxies)
    {
        $this->proxies[] = $proxies;

        return $this;
    }

    /**
     * Remove proxies
     *
     * @param \Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy $proxies
     */
    public function removeProxie(\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy $proxies)
    {
        $this->proxies->removeElement($proxies);
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
        $functionName = 'get'.ucfirst($field);

        $fieldValue = $this->{$functionName}();

        return $fieldValue;
    }
}
