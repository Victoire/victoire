<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page
 *
 * @ORM\Table("vic_page")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\PageBundle\Repository\PageRepository")
 */
class Page extends BasePage
{
    const TYPE = 'page';

    /**
     * contruct
     **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set homepage
     *
     * @param boolean $homepage
     *
     * @return Page
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * is homepage
     *
     * @return bool is homepage
     */
    public function isHomepage()
    {
        return $this->homepage;
    }
}
