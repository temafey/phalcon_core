<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use Engine\Search\Elasticsearch\Query\Builder;

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
     * @param \Engine\Search\Elasticsearch\Query\Builder $dataSource
     * @return string
     */
    public function filter(Builder $dataSource)
    {
        $conditions = [];
		foreach ($this->_filters as $filter) {
			$condition = ($filter instanceof AbstractFilter) ? $filter->filter($dataSource): $filter;
			if ($condition) {
                $conditions[] = $condition;
			}
		}
		if (count($conditions) == 0) {
			return false;
		}
		if (count($conditions) == 1) {
			return $conditions[0];
		}
        //$filter = new \Elastica\Query\Filtered();
        //$filterBool = new \Elastica\Filter
        $filter = new \Elastica\Query\Bool();
		foreach ($conditions as $condition) {
            if ($this->_glue == self::GLUE_AND) {
                $filter->addMust($condition);
            } else {
                $filter->addShould($condition);
            }
        }
        if ($this->_glue == self::GLUE_OR) {
            $filter->setMinimumNumberShouldMatch(1);
        }

		return $filter;
	}

}