<?php

namespace Victoire\Bundle\FilterBundle\Domain;

use Doctrine\ORM\QueryBuilder;

interface BaseFilterInterface
{
    public function buildQuery(QueryBuilder $qb, array $parameters);
}
