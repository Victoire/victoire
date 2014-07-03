<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_page.page_helper
 */
class PageHelper
{
    /**
     * Create an instance of the business entity template page
     *
     * @param BusinessEntityTemplatePage $page   The business entity template page
     * @param string                     $url    The new url
     * @param entity                     $entity The entity
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function createPageInstanceFromBusinessEntityTemplatePage(BusinessEntityTemplatePage $template, $url, $entity)
    {
        //create a new page
        $newPage = new Page();

        $businessEntityTemplate = $template->getBusinessEntityTemplate();
        $parentPage = $businessEntityTemplate->getParentPage();

        //set the page parameter by the business entity template page
        $newPage->setParent($parentPage);
        $newPage->setTemplate($template);

        $newPage->setLayout($businessEntityTemplate->getLayout());

        $newPage->setTitle($url);

        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity);

        $newPage->setEntityProxy($entityProxy);

        return $newPage;
    }
}
