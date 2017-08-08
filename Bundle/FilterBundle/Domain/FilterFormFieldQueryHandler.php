<?php

namespace Victoire\Bundle\FilterBundle\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * Class FilterFormFieldQueryHandler.
 */
class FilterFormFieldQueryHandler
{
    /**
     * @var QueryHelper
     */
    private $queryHelper;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * FilterFormFieldQueryHandler constructor.
     *
     * @param QueryHelper   $queryHelper
     * @param EntityManager $entityManager
     */
    public function __construct(
        QueryHelper $queryHelper,
        EntityManager $entityManager
    ) {
        $this->queryHelper = $queryHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Widget $widgetFilter
     * @param $entity
     * @return array
     */
    public function handle(Widget $widgetFilter, $entity)
    {
        $widgetListing = $widgetFilter->getListing();

        /* first we generate the query for the listing widget */
        $queryBuilder = $this->queryHelper->getQueryBuilder($widgetListing, $this->entityManager);

        $mode = $widgetListing->getMode();

        if ($mode == 'query') {
            /* if listing widget is in query mode we had a subquery with here parameter */
            $queryBuilder = $this->queryHelper->buildWithSubQuery($widgetListing, $queryBuilder, $this->entityManager);
        }


        $subQuery = $queryBuilder->getQuery();
        $subQueryParameters = $queryBuilder->getParameters();

        $filterEntityRepo = $this->entityManager->getRepository($entity);

        $alias = explode('\\', $entity);
        $alias = end($alias);

        /* we build a new query for the filter entity */
        $queryBuilder = $filterEntityRepo->createQueryBuilder($alias);
        /* and give the query for the listing entity has subquery */
        $queryBuilder->andWhere($queryBuilder->expr()->in($alias, $subQuery->getDQL()));

        $parameters = new ArrayCollection(
            array_merge($subQueryParameters->toArray(), $queryBuilder->getParameters()->toArray())
        );
        $queryBuilder->setParameters($parameters);

        $entities = $queryBuilder->getQuery()->getResult();

        return $entities;
    }
}
