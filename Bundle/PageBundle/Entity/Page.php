<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Page
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table("vic_page")
 * @ORM\Entity
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
        parent::_construct();
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
     * Set homepage
     *
     * @return bool is homepage
     */
    public function isHomepage()
    {
        return $this->homepage;
    }
}
