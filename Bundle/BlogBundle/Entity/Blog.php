<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Victoire\Bundle\PageBundle\Entity\Page;
use Doctrine\ORM\Mapping as ORM;

/**
 * PostPage
 *
 * @ORM\Entity
 * @ORM\Table("vic_blog")
 *
 */
class Blog extends Page
{
    const TYPE = 'blog';

}
