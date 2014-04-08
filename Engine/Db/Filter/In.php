<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use \Engine\Mvc\Model\Query\Builder;

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
     * Return filter value string
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    protected function getFilterStr(Builder $dataSource)
    {
        $compare = $this->getCompareCriteria();
        
        $values = $this->_value;
        if(!is_array($values)) {
            $values = [$values];
        }
        $adapter =  $dataSource->getModel()->getReadConnection();
        foreach ($values as &$value) {
            $value = $adapter->escapeString($value);
        }
        if (count($values) == 0) {
            return "";
        }
        
        return $compare."(".implode(", ", $values).")";
    }
}