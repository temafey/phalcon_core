<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use \Engine\Search\Elasticsearch\Query\Builder;

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
	protected $_fields;
	
	/**
	 * Filter value
	 * @var string|integer
	 */
	protected $_value;

	/**
	 * Constructor
	 * 
	 * @param string|array $fields
	 * @param string|integer $value
	 */
	public function __construct($fields, $value)
	{
		$this->_fields = is_array($fields) ? $fields : [$fields => self::CRITERIA_EQ];
		$this->_value = $value;
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
        $filters = [];
        foreach ($this->_fields as $field => $criteria) {
            if ($field === self::COLUMN_ID) {
                $value = (int) $this->_value;
                if (!is_numeric($this->_value)) {
                    continue;
                }
                $field = $model->getPrimary();
            } elseif ($field === self::COLUMN_NAME) {
                $field = $model->getNameExpr();
            }
            if ($criteria === self::CRITERIA_EQ) {
                $filter = new \Elastica\Query\Term();
                $filter->setTerm($field, $this->_value);
                $filters[] = $filter;
            } elseif ($criteria === self::CRITERIA_LIKE) {
                $filter = new \Elastica\Query\Match();
                $filter->setField($field, $this->_value);
                $filters[] = $filter;
            } elseif ($criteria === self::CRITERIA_BEGINS) {
                $filter = new \Elastica\Query\Prefix();
                $filter->setPrefix($field, $this->_value);
                $filters[] = $filter;
            } elseif ($criteria === self::CRITERIA_MORE) {
                $filter = new \Elastica\Query\Range($field, ['from' => $this->_value]);
                $filters[] = $filter;
            } elseif ($criteria === self::CRITERIA_LESS) {
                $filter = new \Elastica\Query\Range($field, ['to' => $this->_value]);
                $filters[] = $filter;
            }
        }

        return $filters;
	}
}