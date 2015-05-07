<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Helper;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Doctrine\ORM\EntityManager;

/**
 * The business entity page pattern helper
 * ref: victoire_business_entity_page.business_entity_page_helper
 */
class BusinessEntityPageHelper
{
    protected $queryHelper = null;
    protected $businessEntityHelper = null;
    protected $parameterConverter = null;
    protected $entityManager = null;

    /**
     * @param QueryHelper          $queryHelper
     * @param BusinessEntityHelper $businessEntityHelper
     * @param ParameterConverter   $parameterConverter
     */
    public function __construct(QueryHelper $queryHelper, BusinessEntityHelper $businessEntityHelper, ParameterConverter $parameterConverter, EntityManager $entityManager)
    {
        $this->queryHelper = $queryHelper;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->parameterConverter = $parameterConverter;
        $this->entityManager = $entityManager;
    }

    /**
     * Is the entity allowed for the business entity page
     *
     * @param BusinessEntityPagePattern                      $bepPattern
     * @param \Victoire\Bundle\PageBundle\Helper\Entity|null $entity
     *
     * @throws \Exception
     * @return boolean
     */
    public function isEntityAllowed(BusinessEntityPagePattern $bepPattern, $entity)
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
     * Get the list of entities allowed for the businessEntityPagePattern page
     *
     * @param BusinessEntityPagePattern $bepPattern
     *
     * @throws \Exception
     * @return array
     */
    public function getEntitiesAllowed(BusinessEntityPagePattern $bepPattern)
    {
        $queryHelper = $this->queryHelper;

        //the base of the query
        $baseQuery = $queryHelper->getQueryBuilder($bepPattern);

        // add this fake condition to ensure that there is always a "where" clause.
        // In query mode, usage of "AND" will be always valid instead of "WHERE"
        $baseQuery->andWhere('1 = 1');

        //filter with the query of the page
        $items = $queryHelper->buildWithSubQuery($bepPattern, $baseQuery)
            ->getQuery()
            ->getResult();

        return $items;
    }

    /**
     * Generate update the page parameters with the entity
     * @param BusinessEntityPagePattern $bepPattern
     * @param Entity                    $entity
     *
     */
    public function generateEntityPageFromPattern(BusinessEntityPagePattern $bepPattern, $entity)
    {
        $page = new BusinessEntityPage();

        $reflect = new \ReflectionClass($bepPattern);
        $patternProperties = $reflect->getProperties();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($patternProperties as $property) {
            if (!in_array($property->getName(), array('id', 'slug', 'widgetMap', 'slots', 'seo', 'i18n')) && !$property->isStatic()) {
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
            $pageUrl = $page->getUrl();
            $pageName = $page->getName();

            //parse the business properties
            foreach ($businessProperties as $businessProperty) {
                $pageUrl = $this->parameterConverter->setBusinessPropertyInstance($pageUrl, $businessProperty, $entity);
                $pageName = $this->parameterConverter->setBusinessPropertyInstance($pageName, $businessProperty, $entity);
            }

            //Check that all twig variables in pattern url was removed for it's generated BusinessEntityPage
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
            $page->setName($pageName);
            $page->setEntityProxy($entityProxy);
            $page->setTemplate($bepPattern);
        }

        return $page;
    }

    /**
     * Get the list of business properties usable for the url
     *
     * @param BusinessEntity $businessEntity
     *
     * @return array The list of business properties
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
     * @param BusinessEntityPagePattern $bepPattern
     *
     * @return integer The position
     */
    public function getIdentifierPositionInUrl(BusinessEntityPagePattern $bepPattern)
    {
        $position = null;

        $url = $bepPattern->getUrl();

        // split on the / character
        $keywords = preg_split("/\//", $url);
        // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $url, $matches);

        //the business property link to the page
        $businessEntityId = $bepPattern->getBusinessEntityName();

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
     * @param  \ReflectionClass $refClass
     * @return View
     */
    public function guessBestViewForEntity($refClass)
    {
        $pattern = null;
        $classname = $refClass->name;
        $businessEntity = $this->businessEntityHelper->findByEntityClassname($classname);
        if ($businessEntity) {
            $patterns = $this->entityManager->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->findByBusinessEntityName($businessEntity->getName());
            if (count($patterns) > 0) {
                $pattern = array_pop($patterns);
            }
        }

        if (!$pattern) {
            $parentRefClass = $refClass->getParentClass();
            if ($parentRefClass) {
                $pattern = $this->guessBestViewForEntity($parentRefClass);
            } else {
                throw new \Exception('Cannot find a BusinessEntityPagePattern that can display the requested BusinessEntity.');
            }
        }

        return $pattern;

    }
}
