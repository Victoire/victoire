<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Victoire\Bundle\PageBundle\Entity\Page;
use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PostPage
 *
 * @ORM\Entity
 *
 */
class Blog extends Page
{
    use \Victoire\Bundle\CoreBundle\Entity\Traits\BusinessEntityTrait;

    const TYPE = 'blog';

}
