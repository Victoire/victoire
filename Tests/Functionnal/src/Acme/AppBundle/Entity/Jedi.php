<?php

namespace Acme\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * Jedi
 *
 * @ORM\Entity
 * @ORM\Table("character_jedi")
 * @VIC\BusinessEntity({"Force"})
 */
class Jedi extends Character
{

    /**
     * @var string
     *
     * @ORM\Column(name="side", type="string", length=55)
     * @VIC\BusinessProperty("textable")
     */
    private $side;

    /**
     * Get side
     *
     * @return string
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * Set side
     * @param string $side
     *
     * @return $this
     */
    public function setSide($side)
    {
        $this->side = $side;

        return $this;
    }
}
