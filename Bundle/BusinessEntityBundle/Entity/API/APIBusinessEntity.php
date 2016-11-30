<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity\API;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * The API business Entity.
 *
 * @ORM\Entity()
 */
class APIBusinessEntity extends BusinessEntity
{
    const TYPE = 'api';

    /**
     * @var string
     *
     * @ORM\Column(name="list", type="string", length=255, nullable=true)
     */
    protected $list = null;

    /**
     * @var string
     *
     * @ORM\Column(name="show", type="string", length=255, nullable=true)
     */
    protected $show = null;

    /**
     * @return string
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param string $list
     */
    public function setList($list)
    {
        $this->list = $list;
    }

    /**
     * @return string
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @param string $show
     */
    public function setShow($show)
    {
        $this->show = $show;
    }
}
