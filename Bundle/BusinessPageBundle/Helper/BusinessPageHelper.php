<?php

namespace Victoire\Bundle\BusinessPageBundle\Helper;

use Doctrine\DBAL\Schema\View;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;

/**
 * The business entity page pattern helper
 * ref: victoire_business_page.business_page_helper.
 */
class BusinessPageHelper
{
    protected $queryHelper = null;
    protected $viewReferenceRepository = null;
    protected $businessEntityHelper = null;
    protected $parameterConverter = null;
    protected $urlBuilder = null;

    /**
     * @param QueryHelper             $queryHelper
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param BusinessEntityHelper    $businessEntityHelper
     * @param ParameterConverter      $parameterConverter
     * @param UrlBuilder              $urlBuilder
     */
    public function __construct(QueryHelper $queryHelper, ViewReferenceRepository $viewReferenceRepository, EntityRepository $businessEntityRepository, BusinessEntityHelper $businessEntityHelper, ParameterConverter $parameterConverter, UrlBuilder $urlBuilder)
    {
        $this->queryHelper = $queryHelper;
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->businessEntityRepository = $businessEntityRepository;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->parameterConverter = $parameterConverter;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Is the entity allowed for the business entity page.
     *
     * @param BusinessTemplate $businessTemplate
     * @param object|null      $entity
     * @param EntityManager    $em
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function isEntityAllowed(BusinessTemplate $businessTemplate, $entity, EntityManager $em = null)
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
        $baseQuery = $queryHelper->getQueryBuilder($businessTemplate, $em);

        $baseQuery->andWhere('main_item.id = '.$entityId);

        //filter with the query of the page
        $items = $queryHelper->buildWithSubQuery($businessTemplate, $baseQuery, $em)
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
     * @param BusinessTemplate $businessTemplate
     * @param EntityManager    $em
     *
     * @return array
     */
    public function getEntitiesAllowed(BusinessTemplate $businessTemplate, EntityManager $em)
    {
        return $this->getEntitiesAllowedQueryBuilder($businessTemplate, $em)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the list of entities allowed for the BusinessTemplate page.
     *
     * @param BusinessTemplate $businessTemplate
     * @param EntityManager    $em
     *
     * @throws \Exception
     *
     * @return QueryBuilder
     */
    public function getEntitiesAllowedQueryBuilder(BusinessTemplate $businessTemplate, EntityManager $em)
    {
        //the base of the query
        $baseQuery = $this->queryHelper->getQueryBuilder($businessTemplate, $em);

        // add this fake condition to ensure that there is always a "where" clause.
        // In query mode, usage of "AND" will be always valid instead of "WHERE"
        $baseQuery->andWhere('1 = 1');

        //filter with the query of the page
        return $this->queryHelper->buildWithSubQuery($businessTemplate, $baseQuery, $em);
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
        $businessProperties = array_merge($businessProperties->toArray(), $seoBusinessProps->toArray());

        return $businessProperties;
    }

    /**
     * Get the position of the identifier in the url of a business entity page pattern.
     *
     * @param BusinessTemplate $businessTemplate
     *
     * @return array The position
     */
    public function getIdentifierPositionInUrl(BusinessTemplate $businessTemplate)
    {
        $position = null;

        $url = $businessTemplate->getUrl();

        // split on the / character
        $keywords = preg_split("/\//", $url);
        // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $url, $matches);

        //the business property link to the page
        $businessEntityId = $businessTemplate->getBusinessEntityName();

        $businessEntity = $this->businessEntityRepository->findBy(['name' => $businessEntityId]);

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
        $templateId = null;
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
            $viewReference = $this->viewReferenceRepository->getOneReferenceByParameters($parameters);
        }

        if (!$viewReference) {
            $parentRefClass = $refClass->getParentClass();
            if ($parentRefClass) {
                $templateId = $this->guessBestPatternIdForEntity($parentRefClass, $entityId, $em, $originalRefClassName);
            } else {
                throw new \Exception(sprintf('Cannot find a BusinessTemplate that can display the requested BusinessEntity ("%s", "%s".)', $refClassName, $entityId));
            }
        } elseif ($viewReference instanceof BusinessPageReference) {
            $templateId = $viewReference->getTemplateId();
        }

        return $templateId;
    }
}
