<?php

namespace Victoire\Bundle\QueryBundle\Entity;

use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

interface VictoireQueryInterface
{
    public function getQuery();

    public function setQuery($query);

    public function getOrderBy();

    public function setOrderBy($orderBy);

    public function getBusinessEntity();

    public function setBusinessEntity(BusinessEntity $businessEntity);
}
