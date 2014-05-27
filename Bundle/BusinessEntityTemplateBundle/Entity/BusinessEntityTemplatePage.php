<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * Page
 *
 * @ORM\Entity
 */
class BusinessEntityTemplatePage extends Page
{
    const TYPE = 'businessEntityTemplate';

    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate", mappedBy="page")
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
