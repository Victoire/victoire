<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Victoire\Bundle\BusinessEntityTemplateBundle\Helper\BusinessEntityTemplateHelper;
use Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Matcher\UrlMatcher;
use Doctrine\Orm\EntityManager;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_page.page_helper
 */
class PageHelper
{
    protected $parameterConverter = null;
    protected $businessEntityHelper = null;
    protected $em; // @doctrine.orm.entity_manager'
    protected $urlHelper; // @victoire_page.url_helper'
    protected $urlMatcher; // @victoire_page.matcher.url_matcher'

    protected $pageParameters = array(
        'title',
        'bodyId',
        'bodyClass',
        'slug',
        'url');

    /**
     * Constructor
     *
     * @param ParameterConverter   $parameterConverter
     * @param BusinessEntityHelper $businessEntityHelper
     */
    public function __construct(
        ParameterConverter $parameterConverter,
        BusinessEntityHelper $businessEntityHelper,
        BusinessEntityTemplateHelper $businessEntityTemplateHelper,
        EntityManager $em,
        UrlHelper $urlHelper,
        UrlMatcher $urlMatcher
    )
    {
        $this->parameterConverter = $parameterConverter;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->em = $em;
        $this->urlHelper = $urlHelper;
        $this->urlMatcher = $urlMatcher;
    }

    /**
     * Create an instance of the business entity template page
     *
     * @param BusinessEntityTemplate $page   The business entity template page
     * @param entity                 $entity The entity
     * @param string                 $url    The new url
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function createPageInstanceFromBusinessEntityTemplate(BusinessEntityTemplate $template, $entity, $url)
    {
        //create a new page
        $newPage = new Page();

        $parentPage = $template->getParent();

        //set the page parameter by the business entity template page
        $newPage->setParent($parentPage);
        $newPage->setTemplate($template);

        $newPage->setLayout($template->getLayout());

        $newPage->setUrl($url);

        $newPage->setTitle($template->getTitle());

        //update the parameters of the page
        $this->updatePageParametersByEntity($newPage, $entity);

        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity);

        $newPage->setEntityProxy($entityProxy);

        return $newPage;
    }

    /**
     * Generate update the page parameters with the entity
     *
     * @param Page   $page
     * @param Entity $entity
     */
    public function updatePageParametersByEntity(Page $page, $entity)
    {
        //if no entity is provided
        if ($entity === null) {
            //we look for the entity of the page
            if ($page->getBusinessEntity() !== null) {
                $entity = $page->getBusinessEntity();
            }
        }

        //only if we have an entity instance
        if ($entity !== null) {
            $className = get_class($entity);

            $businessEntity = $this->businessEntityHelper->findByClassname($className);

            if ($businessEntity !== null) {

                $businessProperties = $this->businessEntityTemplateHelper->getBusinessProperties($businessEntity);

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    //parse of seo attributes
                    foreach ($this->pageParameters as $pageAttribute) {
                        $string = $this->getEntityAttributeValue($page, $pageAttribute);
                        $updatedString = $this->parameterConverter->setBusinessPropertyInstance($string, $businessProperty, $entity);
                        $this->setEntityAttributeValue($page, $pageAttribute, $updatedString);
                    }
                }
            }
        }
    }

    /**
     * If the current page is a business entity template and where are displaying an instance
     * We create a new page for this instance
     * @param Page $page The page of the widget
     *
     * @return Page The page for the entity instance
     */
    public function duplicateTemplatePageIfPageInstance(Page $page)
    {
        //we copy the reference to the widget page
        $widgetPage = $page;

        //services
        $em = $this->em;
        $urlHelper = $this->urlHelper;
        $urlMatcher = $this->urlMatcher;

        //if the url of the referer is not the same as the url of the page of the widget
        //it means we are in a business entity template page and displaying an instance
        $url = $urlHelper->getAjaxUrlRefererWithoutBase();
        $widgetPageUrl = $widgetPage->getUrl();

        //the widget is linked to a page url that is not the current page url
        if ($url !== $widgetPageUrl) {
            //we try to get the page if it exists
            $pageRepository = $em->getRepository('VictoirePageBundle:Page');

            //get the page
            $page = $pageRepository->findOneByUrl($url);

            //no page were found
            if ($page === null) {
                $instance = $urlMatcher->getBusinessEntityTemplateInstanceByUrl($url);

                //an instance of a business entity template and an entity has been identified
                if ($instance !== null) {
                    $template = $instance['businessEntityTemplate'];
                    $entity = $instance['entity'];

                    //so we duplicate the business entity template page for this current instance
                    $page = $this->createPageInstanceFromBusinessEntityTemplate($template, $entity, $url);

                    //the page
                    $em->persist($page);
                    $em->flush();
                } else {
                    //we restore the widget page as the page
                    //we might be editing a template
                    $page = $widgetPage;
                }
            }
        }

        return $page;
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param strin  $functionName
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func(array($entity, $functionName));

        return $fieldValue;
    }

    /**
     * Update the value of the entity
     *
     * @param Object        $entity
     * @param The attribute $field
     * @param string        $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }
}
