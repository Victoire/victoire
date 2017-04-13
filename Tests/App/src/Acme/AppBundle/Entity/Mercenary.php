<?php

namespace Acme\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * Mercenary.
 *
 * @ORM\Entity
 * @VIC\BusinessEntity({"Text", "Force"})
 */
class Mercenary extends Character
{
}
