<?php
namespace Victoire\Bundle\UserBundle\Entity;

use Victoire\Bundle\UserBundle\User as VictoireUserModel;
use Doctrine\ORM\Mapping as ORM;

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
