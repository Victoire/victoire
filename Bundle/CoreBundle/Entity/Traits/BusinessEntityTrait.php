<?php
namespace Victoire\Bundle\CoreBundle\Entity\Traits;

/**
 * BusinessEntity trait adds relationship with the entity proxies
 * @todo Move this into the BusinessEntityBundle
 *
 */
trait BusinessEntityTrait
{

    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy")
     */
    protected $proxy;

    /**
     * proxies relation is inejcted by Victoire\Bundle\CoreBundle\EventSubscriber\EntityProxySubscriber
     * like : OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy", mappedBy="{{businessEntityName}}")
     */
    protected $proxies;

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
     * @return PostPage
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
     * @return PostPage
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
        $functionName = 'get'.ucfirst($field);

        $fieldValue = $this->{$functionName}();

        return $fieldValue;
    }
}
