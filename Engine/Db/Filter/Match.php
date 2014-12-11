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
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    public function filterWhere(Builder $dataSource)
    {
        $adapter =  $dataSource->getModel()->getReadConnection();
        $fields = [];
		foreach ($this->_fields as $field) {
			$fields[] = $adapter->escapeIdentifier($field);
		}
		$expr = implode(',', $fields);
        $this->setBoundParamKey(implode('_', $fields));

		return "MATCH (".$expr.") AGAINST (:".$this->getBoundParamKey().":)";
	}

    /**
     * Return bound params array
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return array
     */
    public function getBoundParams(Builder $dataSource)
    {
        $key = $this->getBoundParamKey();
        $adapter =  $dataSource->getModel()->getReadConnection();
        $this->_expr = $adapter->escapeString($this->_expr);

        return [$key => $this->_expr];
    }

}
