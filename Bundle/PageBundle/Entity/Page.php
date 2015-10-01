<?php

namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page.
 *
 * @ORM\Table("vic_page")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\PageBundle\Repository\PageRepository")
 */
class Page extends BasePage
{
    const TYPE = 'page';

    /**
     * contruct.
     **/
    public function __construct()
    {
        parent::__construct();
    }
}
