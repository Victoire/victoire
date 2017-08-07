<?php

namespace Victoire\Bundle\FilterBundle\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * Class FilterFormFieldQueryHandler
 * @package Victoire\Bundle\FilterBundle\Domain
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
     * @param QueryHelper $queryHelper
     * @param EntityManager $entityManager
     */
    public function __construct(
        QueryHelper $queryHelper,
        EntityManager $entityManager
    )
    {
        $this->queryHelper = $queryHelper;
        $this->entityManager = $entityManager;
    }

    public function handle(Widget $widgetFilter, $entity)
    {
        $widgetListing = $widgetFilter->getListing();

        $queryBuilder = $this->queryHelper->getQueryBuilder($widgetListing, $this->entityManager);

        $mode = $widgetListing->getMode();

        if ($mode == "query") {
            $queryBuilder = $this->queryHelper->buildWithSubQuery($widgetListing, $queryBuilder, $this->entityManager);
        }

        $subQuery = $queryBuilder->getQuery();
        $subQueryParameters = $queryBuilder->getParameters();

        $filterEntityRepo = $this->entityManager->getRepository($entity);

        $aliases = explode("\\", $entity);
        $aliases = end($aliases);

        $queryBuilder = $filterEntityRepo->createQueryBuilder($aliases);
        $queryBuilder->andWhere($queryBuilder->expr()->in($aliases, $subQuery->getDQL()));

        $parameters = new ArrayCollection(
            array_merge($subQueryParameters->toArray(), $queryBuilder->getParameters()->toArray())
        );
        $queryBuilder->setParameters($parameters);

        $entities = $queryBuilder->getQuery()->getResult();

        return $entities;
    }
}