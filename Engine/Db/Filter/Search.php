<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use \Engine\Mvc\Model\Query\Builder;

/**
 * Search filter
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */ 
class Search extends AbstractFilter 
{
	/**
	 * Filter columns
	 * @var array
	 */
	protected $_columns;
	
	/**
	 * Filter value
	 * @var string|integer
	 */
	protected $_value;

	/**
	 * Constructor
	 * 
	 * @param string|array $columns
	 * @param string|integer $value
	 */
	public function __construct($columns, $value) 
	{
		$this->_columns = is_array($columns) ? $columns : [$columns => self::CRITERIA_EQ];
		$this->_value = $value;
	}

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
	public function filterWhere(Builder $dataSource)
	{
		$where = [];
        $adapter =  $dataSource->getModel()->getReadConnection();
		$exprEq = $adapter->escapeString($this->_value);
		$exprLike = $adapter->escapeString("%$this->_value%");
		$exprBegins = $adapter->escapeString("$this->_value%");

		$model = $dataSource->getModel();
		foreach ($this->_columns as $column => $criteria) {		    
			if ($column === self::COLUMN_ID) {
                $alias = $dataSource->getAlias();
				$column = $model->getPrimary();
			} elseif ($column === self::COLUMN_NAME) {
			    $alias = $dataSource->getAlias();
				$column = $model->getNameExpr();
			} else {
			    $alias = $dataSource->getCorrelationName($column);
			}
			if ($criteria === self::CRITERIA_EQ) {
				$where[] = "$alias.$column = $exprEq";
			} elseif ($criteria === self::CRITERIA_LIKE) {
				$where[] = "$alias.$column LIKE $exprLike";
			} elseif ($criteria === self::CRITERIA_BEGINS) {
				$where[] = "$alias.$column LIKE $exprBegins";
			} elseif ($criteria === self::CRITERIA_MORE) {
				$where[] = "`$alias`.`$column` > $exprEq";
			} elseif ($criteria === self::CRITERIA_LESS) {
				$where[] = "$alias.$column < $exprEq";
			}
		}
		if (count($where) > 0) {
			$where = implode(" OR ", $where);
			return "($where)";
		}
		
		return false;
	}
}