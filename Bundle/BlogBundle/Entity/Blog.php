<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Victoire\Bundle\PageBundle\Entity\Page;
use Doctrine\ORM\Mapping as ORM;

/**
 * PostPage
 *
 * @ORM\Entity
 *
 */
class Blog extends Page
{
    const TYPE = 'blog';

}
