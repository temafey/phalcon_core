<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Query;

use Elastica\Filter\BoolAnd,
    Elastica\Query\Bool,
    Elastica\Filter\AbstractFilter as Filter,
    Elastica\Query\AbstractQuery as Query;

/**
 * Class Builder
 *
 * @category    Engine
 * @package     Search
 * @subcategory Elasticsearch
 */
class Builder
{

    /**
     * @var \Engine\Mvc\Model
     */
    protected $_model;

    /**
     * @var \Elastica\Filter\BoolAnd
     */
    protected $_filter;

    /**
     * @var \Elastica\Query\Bool
     */
    protected $_query;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_filter = new BoolAnd();
        $this->_query = new Bool();
    }

    /**
     * Apply new filter condition to query
     *
     * @param \Elastica\Param $condition
     * @throws \Engine\Exception
     * @return \Engine\Search\Elasticsearch\Query\Builder
     */
    public function apply($condition)
    {
        if ($condition instanceof Filter) {
            $this->_filter->addFilter($condition);
        } elseif ($condition instanceof Query) {
            $this->_query->addMust($condition);
        } elseif (is_array($condition)) {
            foreach ($condition as $childCondition) {
                $this->apply($childCondition);
            }
        } else {
            throw new \Engine\Exception('Filter condition not correct');
        }

        return $this;
    }

    /**
     * Return filter object
     *
     * @return \Elastica\Filter\BoolAnd
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Return query object
     *
     * @return \Elastica\Query\Bool
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Set model
     *
     * @param \Engine\Mvc\Model $model
     * @param string $alias
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function setModel(\Engine\Mvc\Model $model)
    {
        $this->_model = $model;

        return $this;
    }

    /**
     * Return model object
     *
     * @return \Engine\Mvc\Model
     */
    public function getModel()
    {
        return $this->_model;
    }
}