<?php
namespace Victoire\Bundle\BusinessPageBundle\Helper;

use Doctrine\DBAL\Schema\View;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Doctrine\ORM\EntityManager;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * The business entity page pattern helper
 * ref: victoire_business_page.business_page_helper
 */
class BusinessPageHelper
{
    protected $queryHelper = null;
    protected $viewCacheHelper = null;
    protected $businessEntityHelper = null;
    protected $parameterConverter = null;
    protected $urlBuilder = null;


    /**
     * @param QueryHelper          $queryHelper
     * @param BusinessEntityHelper $businessEntityHelper
     * @param ParameterConverter   $parameterConverter
     */
    public function __construct(QueryHelper $queryHelper, ViewCacheHelper $viewCacheHelper, BusinessEntityHelper $businessEntityHelper, ParameterConverter $parameterConverter, UrlBuilder $urlBuilder)
    {
        $this->queryHelper = $queryHelper;
        $this->viewCacheHelper = $viewCacheHelper;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->parameterConverter = $parameterConverter;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Is the entity allowed for the business entity page
     *
     * @param BusinessTemplate                      $bepPattern
     * @param \Victoire\Bundle\PageBundle\Helper\Entity|null $entity
     *
     * @throws \Exception
     * @return boolean
     */
    public function isEntityAllowed(BusinessTemplate $bepPattern, $entity)
    {
        $allowed = true;

        //test that an entity is given
        if ($entity === null) {
            throw new \Exception('The entity is required.');
        }

        $queryHelper = $this->queryHelper;

        //the page id
        $entityId = $entity->getId();

        //the base of the query
        $baseQuery = $queryHelper->getQueryBuilder($bepPattern);

        $baseQuery->andWhere('main_item.id = '.$entityId);

        //filter with the query of the page
        $items = $queryHelper->buildWithSubQuery($bepPattern, $baseQuery)
            ->getQuery()->getResult();

        //only one page can be found because we filter on the
        if (count($items) > 1) {
            throw new \Exception('More than 1 item was found, there should not be more than 1 item with this query.');
        }

        if (count($items) === 0) {
            $allowed = false;
        }

        return $allowed;
    }

    /**
     * Get the list of entities allowed for the BusinessTemplate page
     *
     * @param BusinessTemplate $bepPattern
     *
     * @throws \Exception
     * @return array
     */
    public function getEntitiesAllowed(BusinessTemplate $bepPattern, EntityManager $em = null)
    {
        //the base of the query
        $baseQuery = $this->queryHelper->getQueryBuilder($bepPattern, $em);

        // add this fake condition to ensure that there is always a "where" clause.
        // In query mode, usage of "AND" will be always valid instead of "WHERE"
        $baseQuery->andWhere('1 = 1');

        //filter with the query of the page
        $items = $this->queryHelper->buildWithSubQuery($bepPattern, $baseQuery, $em)
            ->getQuery()
            ->getResult();

        return $items;
    }

    /**
     * Generate update the page parameters with the entity
     * @param BusinessTemplate $bepPattern
     * @param Entity                    $entity
     *
     */
    public function generateEntityPageFromPattern(BusinessTemplate $bepPattern, $entity)
    {
        $page = new VirtualBusinessPage();

        $reflect = new \ReflectionClass($bepPattern);
        $patternProperties = $reflect->getProperties();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($patternProperties as $property) {
            if (!in_array($property->getName(), array('id', 'widgetMap', 'slots', 'seo', 'i18n')) && !$property->isStatic()) {
                $value = $accessor->getValue($bepPattern, $property->getName());
                $setMethod = 'set'.ucfirst($property->getName());
                if (method_exists($page, $setMethod)) {
                    $accessor->setValue($page, $property->getName(), $value);
                }
            }
        }

        //find Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity object according to the given $entity
        $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

        if ($businessEntity !== null) {
            //the business properties usable in a url
            $businessProperties = $this->getBusinessProperties($businessEntity);

            //the url of the page
            $pageUrl = $this->urlBuilder->buildUrl($page);

            $pageName = $page->getName();
            $pageSlug = $page->getSlug();

            //parse the business properties
            foreach ($businessProperties as $businessProperty) {
                $pageUrl = $this->parameterConverter->setBusinessPropertyInstance($pageUrl, $businessProperty, $entity);
                $pageSlug = $this->parameterConverter->setBusinessPropertyInstance($pageSlug, $businessProperty, $entity);
                $pageName = $this->parameterConverter->setBusinessPropertyInstance($pageName, $businessProperty, $entity);
            }

            //Check that all twig variables in pattern url was removed for it's generated BusinessPage
            preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $pageUrl, $matches);

            if (count($matches[2])) {
                throw new \Exception(sprintf(
                    'The following identifiers are not defined as well, (%s)
                    you need to add the following lines on your businessEntity properties:
                    <br> <pre>@VIC\BusinessProperty("businessParameter")</pre>',
                    implode($matches[2], ', ')
                ));
            }

            $entityProxy = new EntityProxy();
            $entityProxy->setEntity($entity, $businessEntity->getName());
            //we update the url of the page
            $page->setUrl($pageUrl);
            $page->setSlug($pageSlug);
            $page->setName($pageName);
            $page->setEntityProxy($entityProxy);
            $page->setTemplate($bepPattern);
            if ($seo = $bepPattern->getSeo()) {
                $pageSeo = clone $seo;
                $page->setSeo($pageSeo);
            }
        }

        return $page;
    }

    /**
     * Get the list of business properties usable for the url
     *
     * @param BusinessEntity $businessEntity
     *
     * @return BusinessProperty[] The list of business properties
     */
    public function getBusinessProperties(BusinessEntity $businessEntity)
    {
        //the business properties usable in a url
        $businessProperties = $businessEntity->getBusinessPropertiesByType('businessParameter');

        //the business properties usable in a url
        $seoBusinessProps = $businessEntity->getBusinessPropertiesByType('seoable');

        //the business properties are the identifier and the seoables properties
        $businessProperties = array_merge($businessProperties, $seoBusinessProps);

        return $businessProperties;
    }

    /**
     * Get the position of the identifier in the url of a business entity page pattern
     *
     * @param BusinessTemplate $bepPattern
     *
     * @return integer The position
     */
    public function getIdentifierPositionInUrl(BusinessTemplate $bepPattern)
    {
        $position = null;

        $url = $bepPattern->getUrl();

        // split on the / character
        $keywords = preg_split("/\//", $url);
        // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $url, $matches);

        //the business property link to the page
        $businessEntityId = $bepPattern->getBusinessEntityId();

        $businessEntity = $this->businessEntityHelper->findById($businessEntityId);

        //the business properties usable in a url
        $businessProperties = $businessEntity->getBusinessPropertiesByType('businessParameter');

        //we parse the words of the url
        foreach ($keywords as $index => $keyword) {
            foreach ($businessProperties as $businessProperty) {
                $entityProperty = $businessProperty->getEntityProperty();
                $searchWord = '{{item.'.$entityProperty.'}}';

                if ($searchWord === $keyword) {
                    //the array start at index 0 but we want the position to start at 1
                    $position = array(
                        'position'         => $index + 1,
                        'businessProperty' => $businessProperty
                    );
                }
            }
        }

        return $position;
    }

    /**
     * Guess the best pattern to represent given reflectionClass
     * @param \ReflectionClass $refClass
     * @param integer          $entityId
     * @param string           $originalRefClassName When digging into parentClass, we do not have to forget originalClass to be able to get reference after all
     *
     * @return View
     */
    public function guessBestPatternIdForEntity($refClass, $entityId, $originalRefClassName = null)
    {
        $viewReference = null;
        if (!$originalRefClassName) {
            $originalRefClassName = $refClass->name;
        }

        $businessEntity = $this->businessEntityHelper->findByEntityClassname($refClass->name);

        if ($businessEntity) {
            $parameters = array(
                'entityId' => $entityId,
                'entityNamespace' => $originalRefClassName
            );

            $viewReference = $this->viewCacheHelper->getReferenceByParameters($parameters);
        }

        if (!$viewReference) {
            $parentRefClass = $refClass->getParentClass();
            if ($parentRefClass) {
                $viewReference['patternId'] = $this->guessBestPatternIdForEntity($parentRefClass, $entityId, $originalRefClassName);
            } else {
                throw new \Exception(sprintf('Cannot find a BusinessTemplate that can display the requested BusinessEntity ("%s", "%s".)', $refClass->name, $entityId));
            }
        }

        return $viewReference['patternId'];

    }


}
