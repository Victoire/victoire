<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * BusinessEntityPagePattern
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityPageBundle\Repository\BusinessEntityPageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessEntityPage extends Page
{
    const TYPE = 'business_entity_page';

    /**
     * Auto simple mode: joined entity
     * @var EntityProxy
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="entityProxy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entityProxy;

    /**
     * The entity linked to the page
     * @var unknown
     */
    protected $businessEntity;

    /**
     * The entity static Url page
     * @var unknown
     *
     * @ORM\Column(name="staticUrl", type="text", length=255, nullable=true)
     */
    protected $staticUrl;

    /**
     * Set the entity proxy
     *
     * @param EntityProxy $entityProxy
     */
    public function setEntityProxy($entityProxy)
    {
        $this->entityProxy = $entityProxy;
    }

    /**
     * Get the entity proxy
     *
     * @return EntityProxy
     */
    public function getEntityProxy()
    {
        return $this->entityProxy;
    }

    /**
     * Get the business entity name (PagePattern proxy)
     *
     * @return string
     **/
    public function getBusinessEntityName()
    {
        return $this->getTemplate()->getBusinessEntityName();
    }

    /**
     * Get the business entity
     *
     * @return number
     */
    public function getBusinessEntity()
    {
        //if there is no entity
        if ($this->businessEntity === null) {
            //we try to get one from the proxy
            $entityProxy = $this->getEntityProxy();

            //if there is a proxy
            if ($entityProxy !== null) {
                $businessEntity = $entityProxy->getEntity($this->getBusinessEntityName());
                $this->businessEntity = $businessEntity;
            }
        }

        return $this->businessEntity;
    }

    /**
     * Get staticUrl
     *
     * @return string
     */
    public function getStaticUrl()
    {
        return $this->staticUrl;
    }

    /**
     * Set staticUrl
     *
     * @param string $staticUrl
     *
     * @return $this
     */
    public function setStaticUrl($staticUrl)
    {
        $this->staticUrl = $staticUrl;
        $this->slug = $staticUrl;

        return $this;
    }

}
