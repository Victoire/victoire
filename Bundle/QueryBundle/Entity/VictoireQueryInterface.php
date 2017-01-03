<?php

namespace Victoire\Bundle\QueryBundle\Entity;

interface VictoireQueryInterface
{
    public function getQuery();

    public function setQuery($query);

    public function getOrderBy();

    public function setOrderBy($orderBy);

    public function getBusinessEntityName();

    public function setBusinessEntityName($businessEntityName);
}
