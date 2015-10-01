<?php

namespace Victoire\Bundle\BusinessPageBundle\Helper;

use Doctrine\DBAL\Schema\View;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;

/**
 * The business entity page pattern helper
 * ref: victoire_business_page.business_page_helper.
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
     * Is the entity allowed for the business entity page.
     *
     * @param BusinessTemplate                               $bepPattern
     * @param \Victoire\Bundle\PageBundle\Helper\Entity|null $entity
     * @param EntityManager                                  $em
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function isEntityAllowed(BusinessTemplate $bepPattern, $entity, EntityManager $em = null)
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
        $baseQuery = $queryHelper->getQueryBuilder($bepPattern, $em);

        $baseQuery->andWhere('main_item.id = '.$entityId);

        //filter with the query of the page
        $items = $queryHelper->buildWithSubQuery($bepPattern, $baseQuery, $em)
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
     * Get the list of entities allowed for the BusinessTemplate page.
     *
     * @param BusinessTemplate $bepPattern
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getEntitiesAllowed(BusinessTemplate $bepPattern, EntityManager $em)
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
     * Get the list of business properties usable for the url.
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
     * Get the position of the identifier in the url of a business entity page pattern.
     *
     * @param BusinessTemplate $bepPattern
     *
     * @return int The position
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
                    $position = [
                        'position'         => $index + 1,
                        'businessProperty' => $businessProperty,
                    ];
                }
            }
        }

        return $position;
    }

    /**
     * Guess the best pattern to represent given reflectionClass.
     *
     * @param \ReflectionClass $refClass
     * @param int              $entityId
     * @param EntityManager    $em
     * @param string           $originalRefClassName When digging into parentClass, we do not have to forget originalClass to be able to get reference after all
     *
     * @throws \Exception
     *
     * @return View
     */
    public function guessBestPatternIdForEntity($refClass, $entityId, $em, $originalRefClassName = null)
    {
        $refClassName = $em->getClassMetadata($refClass->name)->name;

        $viewReference = null;
        if (!$originalRefClassName) {
            $originalRefClassName = $refClassName;
        }

        $businessEntity = $this->businessEntityHelper->findByEntityClassname($refClassName);

        if ($businessEntity) {
            $parameters = [
                'entityId'        => $entityId,
                'entityNamespace' => $originalRefClassName,
            ];

            $viewReference = $this->viewCacheHelper->getReferenceByParameters($parameters);
        }

        if (!$viewReference) {
            $parentRefClass = $refClass->getParentClass();
            if ($parentRefClass) {
                $viewReference['patternId'] = $this->guessBestPatternIdForEntity($parentRefClass, $entityId, $em, $originalRefClassName);
            } else {
                throw new \Exception(sprintf('Cannot find a BusinessTemplate that can display the requested BusinessEntity ("%s", "%s".)', $refClassName, $entityId));
            }
        }

        return $viewReference['patternId'];
    }
}
