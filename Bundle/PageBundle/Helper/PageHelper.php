<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\View;
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
                $instance = $this->urlMatcher->getBusinessEntityPageByUrl($url);

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
     * This method get all pages in DB, including instancified patterns related to it's entity
     * @return array the computed pages as array
     */
    public function getAllPages()
    {
        $pages = array();
        //This query is not optimized because we need the property "businessEntityName" later, and it's only present in Pattern pages
        $basePages = $this->em->createQuery("select bp from VictoirePageBundle:BasePage bp")->getResult();
        $businessEntities = $this->businessEntityHelper->getBusinessEntities();

        foreach ($businessEntities as $businessEntity) {
            $properties = $this->businessEntityPageHelper->getBusinessProperties($businessEntity);

            //find businessEdietifiers of the current businessEntity
            $selectableProperties = array('id');
            foreach ($properties as $property) {
                if ($property->getType() === 'businessIdentifier') {
                    $selectableProperties[] = $property->getEntityProperty();
                }
            }
            // This query retrieve business entity object, without useless properties for performance optimisation
            $entities = $this->em->createQuery("select partial
                e.{" . implode(', ', $selectableProperties) . "}
                from ". $businessEntity->getClass() ." e")
                ->getResult();
            // for each business entity
            foreach ($entities as $entity) {
                //and for each page
                foreach ($basePages as $page) {
                    // if page is a pattern, compute it's bep
                    if ($page instanceof BusinessEntityPagePattern) {
                        // only if related pattern entity is the current entity
                        if ($page->getBusinessEntityName() === $businessEntity->getId()) {
                            $currentPattern = clone $page;
                            $this->updatePageParametersByEntity($currentPattern, $entity);
                            $pages['victoire_page_' . $currentPattern->getId() . '_' . $entity->getId()] = array(
                                'url' => $currentPattern->getUrl(),
                                'view' => $currentPattern->getId(),
                                'entity' => $entity->getId(),
                                'entityNamespace' => get_class($entity)
                            );
                        }
                    } else {
                        $pages['victoire_page_' . $page->getId()] = array(
                                'url' => $page->getUrl(),
                                'view' => $page->getId(),
                                'entity' => null,
                                'entityNamespace' => null
                            );
                    }
                }
            }
        }

        return $pages;
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
