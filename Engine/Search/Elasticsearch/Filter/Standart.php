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

        $filterBool = new \Elastica\Query\Bool();
        if ($this->_criteria === self::CRITERIA_EQ) {
            $filter = new \Elastica\Query\Term();
            $filter->setTerm($expr, $this->_value);
            $filterBool->addMust($filter);
        } elseif ($this->_criteria === self::CRITERIA_LIKE) {
            $filter = new \Elastica\Query\QueryString($this->_value);
            $filter->setDefaultField($expr);
            $filterBool->addMust($filter);
        } elseif ($this->_criteria === self::CRITERIA_BEGINS) {
            //$filter = new \Elastica\Query\Prefix();
            //$filter->setPrefix($expr, $this->_value);
            //$filterBool->addMust($filter);
            $filter = new \Elastica\Query\QueryString($this->_value);
            $filter->setDefaultField($expr);
            $filterBool->addMust($filter);
        } elseif ($this->_criteria === self::CRITERIA_MORE) {
            $filter = new \Elastica\Query\Range($expr, ['from' => $this->_value]);
            $filterBool->addMust($filter);
        } elseif ($this->_criteria === self::CRITERIA_LESS) {
            $filter = new \Elastica\Query\Range($expr, ['to' => $this->_value]);
            $filterBool->addMust($filter);
        }

        return $filterBool;

	}
}