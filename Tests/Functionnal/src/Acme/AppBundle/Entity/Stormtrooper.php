<?php

namespace Acme\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * Mercenary
 *
 * @ORM\Entity
 * @ORM\Table("character_stormtrooper")
 * @VIC\BusinessEntity({"LightSaber"})
 */
class Stormtrooper extends Character
{
}
