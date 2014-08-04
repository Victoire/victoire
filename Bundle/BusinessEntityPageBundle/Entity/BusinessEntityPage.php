<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * BusinessEntityPagePattern
 *
 * @ORM\Table("vic_page_business_entity_page")
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
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy", cascade={"persist", "remove"})
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
     * Set the businessEntity
     *
     * @param unknown $businessEntity
     */
    public function setBusinessEntity($businessEntity)
    {
        $this->businessEntity = $businessEntity;
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
                $businessEntity = $entityProxy->getEntity();
                $this->businessEntity = $entityProxy;
            }
        }

        return $this->businessEntity;
    }

    /**
     * Get the page that is a legacy and a business entity page pattern
     *
     * @return Page The page that is a business entity page pattern
     */
    public function getBusinessEntityPagePatternLegacyPage()
    {
        $page = null;

        //is the page a business entity page pattern
        if ($this->getType() === BusinessEntityPagePattern::TYPE) {
            $page = $this;
        } else {
            //we check if the parent is a business entity page pattern
            $parent = $this->getParent();

            if ($parent !== null) {
                $page = $parent->getBusinessEntityPagePatternLegacyPage();
            }
        }

        return $page;
    }
}
