<?php

namespace Victoire\Bundle\QueryBundle\Helper;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\QueryBundle\Entity\VictoireQueryInterface;

/**
 * The QueryHelper helps to build query in Victoire's components
 * ref: victoire_query.query_helper.
 */
class QueryHelper
{
    protected $businessEntityHelper = null;
    protected $currentView;
    protected $reader;

    /**
     * Constructor.
     *
     * @param BusinessEntityHelper $businessEntityHelper
     * @param CurrentViewHelper    $currentView
     * @param Reader               $reader
     */
    public function __construct(BusinessEntityHelper $businessEntityHelper, CurrentViewHelper $currentView, Reader $reader)
    {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->currentView = $currentView;
        $this->reader = $reader;
    }

    /**
     * Get the query builder base. This makes a "select  from item XXX"
     * use the item for doing the left join or where dql.
     *
     * @param \Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate $containerEntity
     *
     * @throws Exception
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(VictoireQueryInterface $containerEntity, EntityManager $em)
    {
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //the business name of the container entity
        $businessEntityId = $containerEntity->getBusinessEntityId();

        //test that there is a business entity name
        if ($businessEntityId === null || $businessEntityId === '') {
            $containerId = $containerEntity->getId();
            throw new \Exception('The container entity ['.$containerId.'] does not have any businessEntityId.');
        }

        //the business class of the container entity
        $businessEntity = $this->businessEntityHelper->findById(strtolower($businessEntityId));

        //test that there was a businessEntity
        if ($businessEntity === null) {
            throw new \Exception('The business entity was not found for the id:['.$businessEntityId.']');
        }

        $businessClass = $businessEntity->getClass();

        $itemsQueryBuilder = $em
            ->createQueryBuilder()
            ->select('main_item')
            ->from($businessClass, 'main_item');

        $refClass = new $businessClass();
        if (method_exists($refClass, 'getDeletedAt')) {
            $itemsQueryBuilder->where('main_item.deletedAt IS NULL');
        }

        return $itemsQueryBuilder;
    }

    /**
     * Get the results from the sql after adding the.
     *
     * @param VictoireQueryInterface $containerEntity
     * @param QueryBuilder           $itemsQueryBuilder
     *
     * @throws \Exception
     *
     * @return QueryBuilder The QB to list of objects
     */
    public function buildWithSubQuery(VictoireQueryInterface $containerEntity, QueryBuilder $itemsQueryBuilder, EntityManager $em)
    {
        //get the query of the container entity
        $query = $containerEntity->getQuery();
        if (method_exists($containerEntity, 'additionnalQueryPart')) {
            $query = $containerEntity->additionnalQueryPart();
        }

        if ($query !== '' && $query !== null) {
            $subQuery = $em->createQueryBuilder()
                                ->select('item.id')
                                ->from($itemsQueryBuilder->getRootEntities()[0], 'item');

            $itemsQueryBuilder->andWhere(
                sprintf('main_item.id IN (%s %s)', $subQuery->getQuery()->getDql(), $query)
            );
        }

        //Add ORDER BY if set
        if ($orderBy = json_decode($containerEntity->getOrderBy(), true)) {
            foreach ($orderBy as $addOrderBy) {
                $reflectionClass = new \ReflectionClass($itemsQueryBuilder->getRootEntities()[0]);
                $reflectionProperty = $reflectionClass->getProperty($addOrderBy['by']);

                //If ordering field is an association, treat it as a boolean
                if ($this->isAssociationField($reflectionProperty)) {
                    $itemsQueryBuilder->addSelect('CASE WHEN main_item.'.$addOrderBy['by'].' IS NULL THEN 0 ELSE 1 END AS HIDDEN caseOrder');
                    $itemsQueryBuilder->addOrderBy('caseOrder', $addOrderBy['order']);
                } else {
                    $itemsQueryBuilder->addOrderBy('main_item.'.$addOrderBy['by'], $addOrderBy['order']);
                }
            }
        }

        $currentView = $this->currentView;

        // If the current page is a BEP, we parse all its properties and inject them as query parameters
        if ($currentView() && $currentView() instanceof BusinessPage && null !== $currentEntity = $currentView()->getBusinessEntity()) {

            // NEW
            $metadatas = $em->getClassMetadata(get_class($currentEntity));
            foreach ($metadatas->fieldMappings as $fieldName => $field) {
                if (strpos($query, ':'.$fieldName) !== false) {
                    $itemsQueryBuilder->setParameter($fieldName, $metadatas->getFieldValue($currentEntity, $fieldName));
                }
            }
            foreach ($metadatas->associationMappings as $fieldName => $field) {
                if (strpos($query, ':'.$fieldName) !== false) {
                    $itemsQueryBuilder->setParameter($fieldName, $metadatas->getFieldValue($currentEntity, $fieldName)->getId());
                }
            }

            if (strpos($query, ':currentEntity') !== false) {
                $itemsQueryBuilder->setParameter('currentEntity', $currentEntity->getId());
            }
        }

        return $itemsQueryBuilder;
    }

    /**
     * Check if field is a OneToOne, OneToMany, ManyToOne or ManyToMany association.
     *
     * @param \ReflectionProperty $field
     *
     * @return bool
     */
    private function isAssociationField(\ReflectionProperty $field)
    {
        $annotations = $this->reader->getPropertyAnnotations($field);
        foreach ($annotations as $key => $annotationObj) {
            if ($annotationObj instanceof OneToOne || $annotationObj instanceof OneToMany || $annotationObj instanceof ManyToOne || $annotationObj instanceof ManyToMany) {
                return true;
            }
        }

        return false;
    }
}
