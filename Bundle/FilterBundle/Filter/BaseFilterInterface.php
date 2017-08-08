<?php

namespace Victoire\Bundle\FilterBundle\Filter;

use Doctrine\ORM\QueryBuilder;

interface BaseFilterInterface
{
    public function buildQuery(QueryBuilder $qb, array $parameters);
}
