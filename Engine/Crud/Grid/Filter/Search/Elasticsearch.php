<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Filter\Search;

use Engine\Crud\Grid\Filter;

/**
 * Class filter grid.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Elasticsearch extends Filter
{

    /**
     * Data container object
     * @var \Engine\Crud\Container\Grid\Mysql\Elasticsearch
     */
    protected $_container;

    /**
     * Apply filters to grid data source object.
     *
     * @param $dataSource
     * @return \Engine\Crud\Grid\Filter
     */
    public function applyFilters($dataSource)
    {
        $elasticSearchDataSource = $this->_container->getElasticDataSource();
        foreach ($this->_fields as $key => $field) {
            $value = (isset($this->_params[$key])) ? $this->_params[$key] : null;
            $field->setValue($value);
            $field->applyFilter($elasticSearchDataSource, $this->_container);
        }

        return $this;
    }
}