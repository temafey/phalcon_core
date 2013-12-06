<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use \Engine\Mvc\Model\Query\Builder;

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
     * Return filter value string
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    protected function getFilterStr(Builder $dataSource)
    {
        $adapter =  $dataSource->getModel()->getReadConnection();
        return $adapter->escapeString($this->_expr);
	}

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    public function filterWhere(Builder $dataSource)
    {
        $adapter =  $dataSource->getModel()->getReadConnection();
        $fields = [];
		foreach($this->_fields as $field){
			$fields[] = $adapter->escapeIdentifier($field);
		}
		$expr = implode(',', $fields);

		return "MATCH (".$expr.") AGAINST (".$this->getFilterStr($dataSource).")";
	}

}
