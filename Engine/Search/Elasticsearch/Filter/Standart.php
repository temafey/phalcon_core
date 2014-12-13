<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use \Engine\Search\Elasticsearch\Query\Builder;

/**
 * Compound filters
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */ 
class Standart extends AbstractFilter 
{
	/**
	 * Filter field
	 * @var array
	 */
	protected $_field;
	
	/**
	 * Filter value
	 * @var string|integer
	 */
	protected $_value;
	
	/**
	 * Filter criteria
	 * @var string
	 */
	protected $_criteria;

	/**
	 * Constructor
	 * 
	 * @param string $field
	 * @param string $value
	 * @param string $criteria
	 */
	public function __construct($field, $value, $criteria = self::CRITERIA_EQ)
	{
		$this->_field = $field;
		$this->_value = $value;
		$this->_criteria = $criteria;
	}

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Search\Elasticsearch\Query\Builder $dataSource
     * @return string
     */
	public function filter(Builder $dataSource)
	{
		$model = $dataSource->getModel();
		if ($this->_field === self::COLUMN_ID) {
            $expr = $model->getPrimary();
		} elseif ($this->_field === self::COLUMN_NAME) {
			$expr = $model->getNameExpr();
		} else {
			$expr = $this->_field;
		}

        if (null === $this->_value) {
            $filter = new \Elastica\Query\Filtered();
            $filterMissing = new \Elastica\Filter\Missing($expr);
            //$filterMissing->addParam("existence", true);
            //$filterMissing->addParam("null_value", true);
            $filter->setFilter($filterMissing);
        } else {
            $filter = new \Elastica\Query\Bool();
            if ($this->_criteria === self::CRITERIA_EQ) {
                $filterTerm = new \Elastica\Query\Term();
                $filterTerm->setTerm($expr, $this->_value);
                $filter->addMust($filterTerm);
            } elseif ($this->_criteria === self::CRITERIA_LIKE) {
                $filterQueryString = new \Elastica\Query\QueryString($this->_value);
                $filterQueryString->setDefaultField($expr);
                $filter->addMust($filterQueryString);
            } elseif ($this->_criteria === self::CRITERIA_BEGINS) {
                //$filter = new \Elastica\Query\Prefix();
                //$filter->setPrefix($expr, $this->_value);
                //$filterBool->addMust($filter);
                $filterQueryString = new \Elastica\Query\QueryString($this->_value);
                $filterQueryString->setDefaultField($expr);
                $filter->addMust($filterQueryString);
            } elseif ($this->_criteria === self::CRITERIA_MORE) {
                $filterRange = new \Elastica\Query\Range($expr, ['from' => $this->_value]);
                $filter->addMust($filterRange);
            } elseif ($this->_criteria === self::CRITERIA_LESS) {
                $filterRange = new \Elastica\Query\Range($expr, ['to' => $this->_value]);
                $filter->addMust($filterRange);
            }
        }

        return $filter;

	}
}