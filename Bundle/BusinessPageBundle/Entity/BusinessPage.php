<?php

namespace Victoire\Bundle\BusinessPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntityInterface;
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
    protected $entity;

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
     * Get the business entity (PagePattern proxy).
     *
     * @return string
     **/
    public function getBusinessEntity()
    {
        return $this->getTemplate()->getBusinessEntity();
    }

    /**
     * Get the business entity.
     *
     * @return BusinessEntityInterface
     */
    public function getEntity()
    {
        //if there is no entity
        if ($this->entity === null) {
            //if there is a proxy
            if ($this->getEntityProxy() !== null) {
                $this->entity = $this->getEntityProxy()->getEntity();

                return $this->entity;
            }
        }

        return $this->entity;
    }
}
