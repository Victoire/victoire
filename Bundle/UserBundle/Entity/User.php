<?php

namespace Victoire\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\UserBundle\Model\User as VictoireUserModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="vic_user")
 */
class User extends VictoireUserModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
