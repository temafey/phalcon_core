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
     * Separate filters
     * @var bool
     */
    protected $_separated;

	/**
	 * Constructor
	 * 
	 * @param string|array $fields
	 * @param string|integer $value
     * @param boolean $separated
	 */
	public function __construct($fields, $value, $separated = true)
	{
		$this->_fields = is_array($fields) ? $fields : [$fields => self::CRITERIA_EQ];
		$this->_value = $value;
        $this->_separated = $separated;
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

        if ($this->_separated) {
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
                if (null === $this->_value) {
                    $filter = new \Elastica\Query\Filtered();
                    $filterMissing = new \Elastica\Filter\Missing($field);
                    //$filterMissing->addParam("existence", true);
                    //$filterMissing->addParam("null_value", true);
                    $filter->setFilter($filterMissing);

                    $filters[] = $filter;
                } else {
                    if ($criteria === self::CRITERIA_EQ) {
                        $filter = new \Elastica\Query\Term();
                        $filter->setTerm($field, $this->_value);
                        $filters[] = $filter;
                    } elseif ($criteria === self::CRITERIA_LIKE) {
                        $filter = new \Elastica\Query\Match();
                        $filter->setField($field, $this->_value);
                        $filters[] = $filter;
                    } elseif ($criteria === self::CRITERIA_BEGINS) {
                        //$filter = new \Elastica\Query\Prefix();
                        //$filter->setPrefix($field, $this->_value);
                        //$filters[] = $filter;
                        $filter = new \Elastica\Query\QueryString();
                        $filter->setQuery($this->_value);
                        $filter->setDefaultField($field);
                        $filters[] = $filter;
                    } elseif ($criteria === self::CRITERIA_MORE) {
                        $filter = new \Elastica\Query\Range($field, ['gt' => $this->_value]);
                        $filters[] = $filter;
                    } elseif ($criteria === self::CRITERIA_LESS) {
                        $filter = new \Elastica\Query\Range($field, ['lt' => $this->_value]);
                        $filters[] = $filter;
                    } elseif ($criteria === self::CRITERIA_MORER) {
                        $filter = new \Elastica\Query\Range($field, ['gte' => $this->_value]);
                        $filters[] = $filter;
                    } elseif ($criteria === self::CRITERIA_LESSER) {
                        $filter = new \Elastica\Query\Range($field, ['lte' => $this->_value]);
                        $filters[] = $filter;
                    }
                }
            }
        } else {
            $filters = new \Elastica\Query\MultiMatch();
            $fields = [];
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
                $fields[] = $field;
            }

            $filters->setFields($fields);
            $filters->setTieBreaker(0.3);
            $filters->setType($filters::TYPE_BEST_FIELDS);
            //$filter->setType($filter::TYPE_MOST_FIELDS);
            $filters->setQuery($this->_value);
        }


        return $filters;
	}
}