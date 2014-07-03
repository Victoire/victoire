<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Entity\Template;

/**
 * Page
 *
 * @ORM\Entity
 */
class BusinessEntityTemplatePage extends Template
{
    const TYPE = 'businessEntityTemplate';

    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate", mappedBy="template")
     */
    protected $businessEntityTemplate;

    /**
     * Set the businessEntityTemplate
     *
     * @param BusinessEntityTemplate $businessEntityTemplate
     */
    public function setBusinessEntityTemplate(BusinessEntityTemplate $businessEntityTemplate)
    {
        $this->businessEntityTemplate = $businessEntityTemplate;
    }

    /**
     * Get the businessEntityTemplate
     *
     * @return BusinessEntityTemplate $businessEntityTemplate
     */
    public function getBusinessEntityTemplate()
    {
        return $this->businessEntityTemplate;
    }
}
