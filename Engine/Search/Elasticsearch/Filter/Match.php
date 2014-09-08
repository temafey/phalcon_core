<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use \Engine\Search\Elasticsearch\Query\Builder;

/**
 *  Match filter
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */
class Match extends AbstractFilter
{
    /**
     * Match fileds
     * @var array
     */
    protected $_field = [];

    /**
     * Filter expression
     * @var string
     */
    protected $_expr;

    /**
     * @param array $fields
     * @param $expr
     */
    public function __construct(array $fields, $expr)
	{
		$this->_expr = $expr;
		$this->_fields = $fields;
	}

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Search\Elasticsearch\Query\Builder $dataSource
     * @return string
     */
    public function filter(Builder $dataSource)
    {

        $query = new \Elastica\Query\MultiMatch();
        $query->setFields($this->_fields);
        $query->setQuery($this->_expr);

		return new \Elastica\Filter\Query($query);
	}

}
