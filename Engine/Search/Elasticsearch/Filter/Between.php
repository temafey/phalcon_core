<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use \Engine\Search\Elasticsearch\Query\Builder;

/**
 *  Between filter
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */
class Between extends Standart
{
    /**
     * Filter field
     * @var string
     */
    protected $_field;

    /**
     * Max value
     * @var string|integer
     */
    protected $_min;

    /**
     * Max value
     * @var string|integer
     */
    protected $_max;

    /**
     * @var string
     */
    protected $_criteria;

    /**
     * @param string $field
     * @param string|integer $min
     * @param string|integer $max
     * @param string $criteria
     */
    public function __construct($field, $min, $max, $criteria = self::CRITERIA_EQ)
    {
        $this->_field = $field;
        $this->_min = $min;
        $this->_max = $max;
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
        $filter = new \Elastica\Query\Range($this->_field, ['gte' => $this->_min, 'lte' => $this->_max]);

        return $filter;
    }

}
