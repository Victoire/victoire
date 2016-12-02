<?php

namespace Victoire\Bundle\BusinessPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * BusinessPage.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessPageBundle\Repository\BusinessPageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessPage extends Page
{
    const TYPE = 'business_page';

    /**
     * Auto simple mode: joined entity.
     *
     * @var EntityProxy
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="entityProxy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entityProxy;

    /**
     * The entity linked to the page.
     *
     * @var object
     */
    protected $businessEntity;

    /**
     * Set the entity proxy.
     *
     * @param EntityProxy $entityProxy
     */
    public function setEntityProxy($entityProxy)
    {
        $this->entityProxy = $entityProxy;
    }

    /**
     * Get the entity proxy.
     *
     * @return EntityProxy
     */
    public function getEntityProxy()
    {
        return $this->entityProxy;
    }

    /**
     * Get the business entity name (PagePattern proxy).
     *
     * @return string
     **/
    public function getBusinessEntityName()
    {
        return $this->getTemplate()->getBusinessEntityName();
    }

    /**
     * Get the business entity.
     *
     * @return number
     */
    public function getBusinessEntity()
    {
        //if there is no entity
        if ($this->businessEntity === null) {
            //if there is a proxy
            if ($this->getEntityProxy() !== null) {
                $this->businessEntity = $this->getEntityProxy()->getEntity();

                return $this->businessEntity;
            }
        }

        return $this->businessEntity;
    }
}
