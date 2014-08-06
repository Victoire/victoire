<?php

namespace Victoire\Bundle\TemplateBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\View;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Template
 *
 * @ORM\Entity
 */
class Template extends View
{
    const TYPE = 'template';

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\TemplateBundle\Entity\Template", mappedBy="template")
     */
    protected $inheritors;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\PageBundle\Entity\BasePage", mappedBy="template")
     */
    protected $pages;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="layout", type="string", length=255)
     */
    protected $layout;

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
     * Get pages (all Pages having this object as Template)
     *
     * @return string
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set page
     * @param string $inheritors
     *
     * @return Template
     */
    public function setInheritors($inheritors)
    {
        $this->inheritors = $inheritors;

        return $this;
    }

    /**
     * Get inheritors (all Templates having this object as Template)
     *
     * @return string
     */
    public function getInheritors()
    {
        return $this->inheritors;
    }

    /**
     * Set layout
     * @param string $layout
     *
     * @return Template
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
