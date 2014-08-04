<?php

namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * Template
 *
 * @ORM\Table("vic_page_template")
 * @ORM\Entity
 */
class Template extends View
{

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\PageBundle\Entity\BasePage", mappedBy="template")
     */
    protected $pages;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\PageBundle\Entity\Template", mappedBy="template")
     */
    protected $children;

    /**
     * contruct
     **/
    public function __construct()
    {
        parent::__construct();
        $this->widgets = new ArrayCollection();
    }

    /**
     * Set page
     * @param string $pages
     *
     * @return Template
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Get pages
     *
     * @return string
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set children
     * @param string $children
     *
     * @return Template
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get children
     *
     * @return string
     */
    public function getChildren()
    {
        return $this->children;
    }
}
