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
     * @var string
     *
     * @ORM\Column(name="homepage", type="boolean", nullable=false)
     */
    protected $homepage;

    /**
     * contruct
     **/
    public function __construct()
    {
        parent::__construct();
        $this->homepage = false;
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

    /**
     * get url
     *
     * @return string override to test if page is homepage
     */
    public function getUrl()
    {
        return $this->homepage ? "" : parent::getUrl();
    }
}
