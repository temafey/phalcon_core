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


    /**
     * Return bound params array
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return array
     */
    public function getBoundParams(Builder $dataSource)
    {
        $params = [];
        $returnNull = false;
        foreach ($this->_filters as $filter) {
            $filterParams = $filter->getBoundParams($dataSource);
            if (null === $filterParams) {
                $returnNull = true;
            }
            if ($filterParams) {
                foreach ($filterParams as $key => $value) {
                    if (isset($params[$key])) {
                        if ($value != $params[$key]) {
                            throw new \Engine\Exception("Filter '{$filter->getKey()}' with bound param '{$key}' has more then one value '{$params[$key]}' and '{$value}'");
                        }
                    } else {
                        $params[$key] = $value;
                    }
                }
            }
        }

        if (count($params) == 0) {
            return $returnNull;
        }

        return $params;
    }

}