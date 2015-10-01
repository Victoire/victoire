<?php

namespace Victoire\Bundle\QueryBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;

/**
 * The QueryHelper helps to build query in Victoire's components
 * ref: victoire_query.query_helper.
 */
class QueryHelper
{
    protected $businessEntityHelper = null;
    protected $currentView;

    /**
     * Constructor.
     *
     * @param BusinessEntityHelper $businessEntityHelper
     * @param CurrentViewHelper    $currentView
     */
    public function __construct(BusinessEntityHelper $businessEntityHelper, CurrentViewHelper $currentView)
    {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->currentView = $currentView;
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
    public function getQueryBuilder($containerEntity, EntityManager $em)
    {
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //verify that the object has the query trait
        $this->checkObjectHasQueryTrait($containerEntity);

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
     * Check that the object is not null and has the query trait.
     *
     * @param \Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate $containerEntity
     *
     * @throws \Exception
     */
    protected function checkObjectHasQueryTrait($containerEntity)
    {
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //test that the containerEntity has the trait
        if (!method_exists($containerEntity, 'getQuery') || !method_exists($containerEntity, 'getBusinessEntityId')) {
            throw new \Exception('The object '.get_class($containerEntity).' does not have the QueryTrait.');
        }
    }

    /**
     * Get the results from the sql after adding the.
     *
     * @param mixed        $containerEntity
     * @param QueryBuilder $itemsQueryBuilder
     *
     * @throws \Exception
     *
     * @return QueryBuilder The QB to list of objects
     */
    public function buildWithSubQuery($containerEntity, QueryBuilder $itemsQueryBuilder, EntityManager $em)
    {
        //test the container entity
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //verify that the object has the query trait
        //@todo please use an interface and cast with it in the method signature
        $this->checkObjectHasQueryTrait($containerEntity);

        //get the query of the container entity
        $query = $containerEntity->getQuery();
        if (method_exists($containerEntity, 'additionnalQueryPart')) {
            $query = $containerEntity->additionnalQueryPart();
        }
        $orderBy = json_decode($containerEntity->getOrderBy(), true);
        if ($query !== '' && $query !== null) {
            $subQuery = $em->createQueryBuilder()
                                ->select('item.id')
                                ->from($itemsQueryBuilder->getRootEntities()[0], 'item');

            $itemsQueryBuilder
                ->andWhere('main_item.id IN ('.$subQuery->getQuery()->getDql().' '.$query.')');
            if ($orderBy) {
                foreach ($orderBy as $addOrderBy) {
                    $itemsQueryBuilder->addOrderBy('main_item.'.$addOrderBy['by'], $addOrderBy['order']);
                }
            }
        }

        if (method_exists($containerEntity, 'getOrderBy')) {
            $orderBy = json_decode($containerEntity->getOrderBy(), true);
            if ($orderBy) {
                foreach ($orderBy as $addOrderBy) {
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
}
