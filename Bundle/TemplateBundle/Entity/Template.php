<?php

namespace Victoire\Bundle\TemplateBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * Template.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\TemplateBundle\Repository\TemplateRepository")
 */
class Template extends View
{
    const TYPE = 'template';

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", mappedBy="template")
     */
    protected $inheritors;

    /**
     * @var string
     *
     * @ORM\Column(name="layout", type="string", length=255)
     */
    protected $layout;

    /**
     * Construct.
     **/
    public function __construct()
    {
        parent::__construct();
        $this->widgets = new ArrayCollection();
        $this->inheritors = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }

    /**
     * to string.
     *
     * @return string
     **/
    public function __toString()
    {
        return 'Modèle > '.$this->name;
    }

    /**
     * add page.
     *
     * @param BasePage $page
     *
     * @return Template
     **/
    public function addPage(BasePage $page)
    {
        $page->setTemplate($this);
        $this->pages[] = $page;

        return $this;
    }

    /**
     * set page.
     *
     * @param array $pages
     *
     * @return Template
     **/
    public function setPages(array $pages)
    {
        foreach ($pages as $page) {
            $this->addPage($page);
        }

        return $this;
    }

    /**
     * remove page.
     *
     * @param BasePage $page
     *
     * @return Template
     **/
    public function removePage($page)
    {
        $this->pages->removeElement($page);

        return $this;
    }

    /**
     * Get pages (all Pages having this object as Template).
     *
     * @return ArrayCollection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set layout.
     *
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
     * Get layout.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set inheritors.
     *
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
     * Get inheritors (all Views having this object as Template).
     *
     * @return [View]
     */
    public function getInheritors()
    {
        return $this->inheritors;
    }

    /**
     * Get inheritors (all Templates having this object as Template).
     *
     * @return [Template]
     */
    public function getTemplateInheritors()
    {
        $templateInheritors = [];
        foreach ($this->inheritors as $inheritor) {
            if ($inheritor instanceof self) {
                $templateInheritors[] = $inheritor;
            }
        }

        return $templateInheritors;
    }

    /**
     * @Assert\Callback(groups={"victoire"})
     */
    public function validate(ExecutionContextInterface $context)
    {
        $template = $this;
        $templateHasLayout = false;
        while ($template != null) {
            if ($template->getLayout() != null) {
                $templateHasLayout = true;

                return;
            }
            $template = $template->getTemplate();
        }

        if ($this->getLayout() == null) {
            $context->addViolationAt(
                'layout',
                'data.template.templateform.view.type.template.layout.validator_message',
                [],
                null
            );
        }
    }
}
