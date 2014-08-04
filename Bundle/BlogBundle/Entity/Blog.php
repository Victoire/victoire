<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * PostPage
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity
 * @ORM\Table("vic_blog")
 *
 */
class Blog extends BasePage
{
    const TYPE = 'blog';

}
