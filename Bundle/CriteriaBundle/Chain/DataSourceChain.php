<?php

namespace Victoire\Bundle\CriteriaBundle\Chain;



/**
 * DataSource chain.
 */
class DataSourceChain
{
    private $dataSource;

    public function __construct()
    {
        $this->dataSource = [];
    }

    /**
     * @param $dataSource
     * @param $alias
     */
    public function addDataSource($dataSource, $parameters)
    {
        $method = $parameters['method'];
        $data = function() use ($dataSource, $method) {
            return $dataSource->{$method}();
        };
        $this->dataSource[$parameters['alias']] = [
            'data' => $data,
            'dataSource' => $dataSource,
            'parameters' => $parameters,
        ];

    }

    /**
     * @param string $alias
     *
     */
    public function getData($alias)
    {
        return $this->dataSource[$alias]['data'];
    }
    /**
     * @param string $alias
     *
     */
    public function getDataSource($alias)
    {
        return $this->dataSource[$alias]['dataSource'];
    }
    /**
     * @param string $alias
     *
     */
    public function getDataSourceParameters($alias)
    {
        return $this->dataSource[$alias]['parameters'];
    }

    /**
     * @return array
     */
    public function getDataSources()
    {
        return $this->dataSource;
    }
}
