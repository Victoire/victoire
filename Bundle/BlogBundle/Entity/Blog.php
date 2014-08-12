<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * PostPage
 *
 * @ORM\Entity
 * @ORM\Table("vic_blog")
 *
 */
class Blog extends BasePage
{
    const TYPE = 'blog';

}
