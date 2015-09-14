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

    //@todo Make it dynamic please
    protected $pageParameters = array(
        'name',
        'bodyId',
        'bodyClass',
        'slug',
        'url',
        'locale',
    );

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
    public function getEntitiesAllowed(BusinessTemplate $bepPattern)
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

    /**
     * Generate update the page parameters with the entity
     *
     * @param BasePage $page
     * @param Entity   $entity
     */
    public function updatePageParametersByEntity(BusinessPage $page, $entity)
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
            $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

            if ($businessEntity !== null) {
                $businessProperties = $this->getBusinessProperties($businessEntity);

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    //parse of seo attributes
                    foreach ($this->pageParameters as $pageAttribute) {
                        $string = $this->getEntityAttributeValue($page, $pageAttribute);
                        $updatedString = $this->parameterConverter->setBusinessPropertyInstance($string, $businessProperty, $entity);
                        $this->setEntityAttributeValue($page, $pageAttribute, $updatedString);
                    }
                }

                $urlizer = new Urlizer();
                $page->setSlug($urlizer->urlize($page->getName()));
            }
        }
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param BusinessPage $entity
     * @param strin              $field
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
     * @param BusinessPage $entity
     * @param string             $field
     * @param string             $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }
}
