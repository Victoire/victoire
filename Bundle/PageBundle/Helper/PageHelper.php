<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Matcher\UrlMatcher;

/**
 * Page helper
 * ref: victoire_page.page_helper
 */
class PageHelper
{
    protected $parameterConverter = null;
    protected $businessEntityHelper = null;
    protected $em; // @doctrine.orm.entity_manager'
    protected $urlHelper; // @victoire_page.url_helper'
    protected $urlMatcher; // @victoire_page.matcher.url_matcher'

    //@todo Make it dynamic please
    protected $pageParameters = array(
        'name',
        'bodyId',
        'bodyClass',
        'slug',
        'url'
    );

    /**
     * Constructor
     * @param ParameterConverter       $parameterConverter
     * @param BusinessEntityHelper     $businessEntityHelper
     * @param BusinessEntityPageHelper $businessEntityPageHelper
     * @param EntityManager            $em
     * @param UrlHelper                $urlHelper
     * @param UrlMatcher               $urlMatcher
     */
    public function __construct(
        ParameterConverter $parameterConverter,
        BusinessEntityHelper $businessEntityHelper,
        BusinessEntityPageHelper $businessEntityPageHelper,
        EntityManager $em,
        UrlHelper $urlHelper,
        UrlMatcher $urlMatcher
    )
    {
        $this->parameterConverter = $parameterConverter;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->businessEntityPageHelper = $businessEntityPageHelper;
        $this->em = $em;
        $this->urlHelper = $urlHelper;
        $this->urlMatcher = $urlMatcher;
    }

    /**
     * Create an instance of the business entity page
     * @param BusinessEntityPagePattern $businessEntityPagePattern The business entity page
     * @param entity                    $entity                    The entity
     * @param string                    $url                       The new url
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function createPageInstanceFromBusinessEntityPagePattern(BusinessEntityPagePattern $businessEntityPagePattern, $entity, $url)
    {
        //create a new page
        $newPage = new Page();

        $parentPage = $businessEntityPagePattern->getParent();

        //set the page parameter by the business entity page
        $newPage->setParent($parentPage);
        $newPage->setTemplate($businessEntityPagePattern);
        $newPage->setUrl($url);

        $newPage->setTitle($businessEntityPagePattern->getTitle());

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
     * @param BasePage $page
     * @param Entity   $entity
     */
    public function updatePageParametersByEntity(BasePage $page, $entity)
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

                $businessProperties = $this->businessEntityPageHelper->getBusinessProperties($businessEntity);

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
     * If the current page is a business entity page pattern and where are displaying an instance
     * We create a new page for this instance
     * @param Page $page The page of the widget
     *
     * @return Page The page for the entity instance
     */
    public function duplicatePagePatternIfPageInstance(View $page)
    {
        //we copy the reference to the widget page
        $widgetPage = $page;

        //services
        $em = $this->em;

        //if the url of the referer is not the same as the url of the page of the widget
        //it means we are in a business entity template page and displaying an instance
        $url = $this->urlHelper->getAjaxUrlRefererWithoutBase();
        $widgetPageUrl = $widgetPage->getUrl();

        //the widget is linked to a page url that is not the current page url
        if ($url !== $widgetPageUrl) {
            //we try to get the page if it exists
            $pageRepository = $em->getRepository('VictoirePageBundle:Page');

            //get the page
            $page = $pageRepository->findOneByUrl($url);

            //no page were found
            if ($page === null) {
                $instance = $this->urlMatcher->getBusinessEntityPagePatternInstanceByUrl($url);

                //an instance of a business entity page pattern and an entity has been identified
                if ($instance !== null) {
                    $template = $instance['businessEntityPagePattern'];
                    $entity = $instance['entity'];
                    //so we duplicate the business entity page for this current instance
                    $page = $this->createPageInstanceFromBusinessEntityPagePattern($template, $entity, $url);

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
     * @param strin  $field
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
     * @param Object $entity
     * @param string $field
     * @param string $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }
}
