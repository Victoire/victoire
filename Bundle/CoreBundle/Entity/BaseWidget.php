<?php
namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * undocumented class
 * @ORM\MappedSuperclass
 **/
abstract class BaseWidget
{

    /**
     * Auto simple mode: joined entity
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy", inversedBy="widget", cascade={"persist"})
     */
    protected $entity;

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
