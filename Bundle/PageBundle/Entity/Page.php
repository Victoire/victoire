<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;

/**
 * Page
 *
 * @ORM\Entity
 */
class Page extends BasePage
{
    const TYPE = 'page';

    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate", mappedBy="parentPage")
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
