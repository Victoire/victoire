<?php

namespace Victoire\Bundle\FilterBundle\Filter\Chain;

use Symfony\Component\Form\AbstractType;

class FilterChain
{

    private $filters;

    public function __construct()
    {
        $this->filters = array();
    }

    public function addFilter(AbstractType $filter)
    {
        $this->filters[$filter->getName()] = $filter;
    }

    public function getFilters()
    {
        return $this->filters;
    }
}
