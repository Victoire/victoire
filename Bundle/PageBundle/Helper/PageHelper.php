<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;


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
     * @param BusinessEntityTemplatePage $page The business entity template page
     * @param string                     $url  The new url
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function createPageInstanceFromBusinessEntityTemplatePage(BusinessEntityTemplatePage $page, $url)
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

        return $newPage;
    }
}
