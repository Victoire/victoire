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
    public function createPageInstanceFromBusinessEntityTemplatePage(BusinessEntityTemplatePage $page, $url, $entity)
    {
        //create a new page
        $newPage = new Page();

        //set the page parameter by the business entity template page
        $newPage->setParent($page);
        $newPage->setLayout($page->getLayout());

        $newPage->setTitle($page->getTitle());
        $newPage->setUrl($url);

        //the slug is a copy of the url of the copy and the current url
        $slug = $page->getSlug().'-'.$newPage->getUrl();
        $newPage->setSlug($slug);

        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity);

        $newPage->setEntityProxy($entityProxy);

        return $newPage;
    }
}
