<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;



abstract class BaseDataSourceProxy
{
    protected $dataSource;

    public function __construct($dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function __call($name, $args = null)
    {
        $this->dataSource->{$name}($args);
    }
}
