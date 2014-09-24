<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use \Engine\Search\Elasticsearch\Query\Builder;

/**
 *  Match filter
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */
class In extends Standart
{
    CONST CRITERIA_EQ = "IN";
    CONST CRITERIA_NOTEQ = "NOTIN";

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Search\Elasticsearch\Query\Builder $dataSource
     * @return string
     */
    public function filter(Builder $dataSource)
    {
        $values = $this->_value;
        if (!is_array($values)) {
            $values = [$values];
        }

        if (count($values) == 0) {
            return "";
        }

        $filter	= new \Elastica\Query\Terms($this->_field, $values);
        if ($this->_criteria == self::CRITERIA_NOTEQ) {
            $chlFilter = $filter;
            $filter = new \Elastica\Query\Bool();
            $filter->addMustNot($chlFilter);
        }

        return $filter;
    }
}