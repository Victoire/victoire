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
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern", inversedBy="instances")
     * @ORM\JoinColumn(name="business_entity_page_pattern_id", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    protected $pattern;

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

    public function setPattern(BusinessEntityPagePattern $businessEntityPagePattern) { $this->businessEntityPagePattern = $businessEntityPagePattern; return $this; }
    public function getPattern() { return $this->businessEntityPagePattern; }

    /**
     * Get the business entity name (PagePattern proxy)
     *
     * @return string
     **/
    public function getBusinessEntityName()
    {
        return $this->getPattern()->getBusinessEntityName();
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
                $businessEntity = $entityProxy->getEntity($this->getBusinessEntityName());
                $this->businessEntity = $entityProxy;
            }
        }

        return $this->businessEntity;
    }
}
