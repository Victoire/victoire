<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * BusinessEntityPagePattern
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityPageBundle\Repository\BusinessEntityPageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessEntityPage extends BasePage
{
    const TYPE = 'business_entity_page';

    /**
     * Auto simple mode: joined entity
     * @var EntityProxy
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy", cascade={"persist", "remove"})
     */
    protected $entityProxy;

    /**
     * The entity linked to the page
     * @var unknown
     */
    protected $businessEntity;

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
}
