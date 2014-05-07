<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page
 *
 * @ORM\Entity
 */
class Page extends BasePage
{
    const TYPE = 'page';

}
