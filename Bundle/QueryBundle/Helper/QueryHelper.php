<?php

namespace Victoire\Bundle\QueryBundle\Helper;


use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Doctrine\ORM\QueryBuilder;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_query.query_helper
 */
class QueryHelper
{
    protected $em = null;
    protected $businessEntityHelper = null;

    /**
     * Constructor
     *
     * @param EntityManager        $em
     * @param BusinessEntityHelper $businessEntityHelper
     */
    public function __construct(EntityManager $em, BusinessEntityHelper $businessEntityHelper)
    {
        $this->em = $em;
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * Get the query builder base. This makes a "select  from item XXX"
     * use the item for doing the left join or where dql
     *
     * @param unknown $containerEntity
     *
     * @return QueryBuilder
     *
     * @throws Exception
     */
    public function getQueryBuilder($containerEntity)
    {
        //services
        $em = $this->em;
        $businessEntityHelper = $this->businessEntityHelper;

        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //verify that the object has the query trait
        $this->checkObjectHasQueryTrait($containerEntity);

        //the business name of the container entity
        $businessEntityName = $containerEntity->getBusinessEntityName();

        //test that there is a business entity name
        if ($businessEntityName === null || $businessEntityName === '') {
            throw new \Exception('The container entity does not have any businessEntityName.');
        }

        //the business class of the container entity
        $businessEntity = $businessEntityHelper->findById($businessEntityName);

        //test that there was a businessEntity
        if ($businessEntity === null) {
            throw new \Exception('The business entity was not found for the id:['.$businessEntityName.']');
        }

        $businessClass = $businessEntity->getClass();

        $itemsQueryBuilder = $em
            ->createQueryBuilder()
            ->select('item')
            ->from($businessClass, 'item');

        return $itemsQueryBuilder;
    }

    /**
     * Check that the object is not null and has the query trait
     * @param unknown $containerEntity
     * @throws \Exception
     */
    protected function checkObjectHasQueryTrait($containerEntity)
    {
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }


        //test that the containerEntity has the trait
        if (!method_exists($containerEntity, 'getQuery') || !method_exists($containerEntity, 'getBusinessEntityName')) {
            throw new \Exception('The object '.get_class($containerEntity).' does not have the QueryTrait.');
        }
    }

    /**
     * Get the results from the sql after adding the
     *
     * @param unknown $containerEntity
     * @param unknown $queryBuilder
     * @param string  $additionnalDql
     * @throws \Exception
     *
     * @return array The list of objects
     */
    public function getResultsAddingSubQuery($containerEntity, QueryBuilder $itemsQueryBuilder, $additionnalDql = '')
    {
        //services
        $em = $this->em;

        //test the container entity
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //verify that the object has the query trait
        $this->checkObjectHasQueryTrait($containerEntity);

        //get the query of the container entity
        $query = $containerEntity->getQuery();

        if ($query !== '' && $query !== null) {
            $query = 'AND '.$query;
        }
        if ($additionnalDql !== '' && $additionnalDql !== null) {
            $additionnalDql = 'AND '.$additionnalDql;
        }

        //we add the query
        $itemsQuery = $itemsQueryBuilder->getQuery()->getDQL() . " " . $query. " ".$additionnalDql;

        $items = $em->createQuery($itemsQuery)->setParameters($itemsQueryBuilder->getParameters())->getResult();

        return $items;
    }
}
