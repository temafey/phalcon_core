<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use Engine\Mvc\Model\Query\Builder;

/**
 * Compound filters
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */ 
class Compound extends AbstractFilter 
{
	CONST GLUE_OR = 'OR';
    CONST GLUE_AND = 'AND';
	
	/**
	 * Filters
	 * @var array
	 */
	protected $_filters;
	
	/**
	 * Filters glue
	 * @var string
	 */
	protected $_glue;

	/**
	 * Constructor
	 * 
	 * @param string $glue
	 * @param array $filters
	 */
	public function __construct($glue = self::GLUE_OR, array $filters) 
	{
		$this->_glue = $glue;
		$this->_filters = $filters;
	}

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    public function filterWhere(Builder $dataSource)
    {
		$wheres = [];
		foreach ($this->_filters as $filter) {
			$where = ($filter instanceof AbstractFilter) ? $filter->filterWhere($dataSource): $filter;
			if ($where) {
				$wheres[] = $where;
			}
		}
		
		if(count($wheres) == 0) {
			return false;
		}
		if(count($wheres) == 1) {
			return $wheres[0];
		}
		$where = "(".implode(" $this->_glue ", $wheres).")";
		
		return $where;
	}

}