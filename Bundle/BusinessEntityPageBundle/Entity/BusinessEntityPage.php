<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\BaseEntityProxy;
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
     * @var BaseEntityProxy
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="entityProxy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entityProxy;

    /**
     * The entity linked to the page
     * @var object
     */
    protected $businessEntity;

    /**
     * The entity static Url page
     * @var string
     *
     * @ORM\Column(name="staticUrl", type="text", length=255, nullable=true)
     */
    protected $staticUrl;

    /**
     * Set the entity proxy
     *
     * @param BaseEntityProxy $entityProxy
     */
    public function setEntityProxy($entityProxy)
    {
        $this->entityProxy = $entityProxy;
    }

    /**
     * Get the entity proxy
     *
     * @return BaseEntityProxy
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
    public function getBusinessEntityId()
    {
        return $this->getTemplate()->getBusinessEntityId();
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
            //if there is a proxy
            if ($this->getEntityProxy() !== null) {
                $this->businessEntity = $this->getEntityProxy()->getEntity($this->getBusinessEntityId());

                return $this->businessEntity;
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

        return $this;
    }

}
