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
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    public function filterWhere(Builder $dataSource)
    {
        $model = $dataSource->getModel();
        if ($this->_column === self::COLUMN_ID) {
            $expr = $model->getPrimary();
            $alias = $dataSource->getAlias();
        } elseif ($this->_column === self::COLUMN_NAME) {
            $expr = $model->getNameExpr();
            $alias = $dataSource->getAlias();
        } else {
            $expr = $this->_column;
            $alias = $dataSource->getCorrelationName($this->_column);
        }
        if (!$alias) {
            throw new \Engine\Exception("Field '".$this->_column."' not found in query builder");
        }
        $compare = $this->getCompareCriteria();
        $this->setBoundParamKey($alias."_".$expr);

        return $alias.".".$expr." ".$compare." (:".$this->getBoundParamKey().":)";
    }

    /**
     * Return bound params array
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return array
     */
    public function getBoundParams(Builder $dataSource)
    {
        $key = $this->getBoundParamKey();

        $values = $this->_value;
        if (!is_array($values)) {
            $values = [$values];
        }
        $adapter =  $dataSource->getModel()->getReadConnection();
        foreach ($values as &$value) {
            $value = $adapter->escapeString($value);
        }
        if (count($values) == 0) {
            return false;
        }

        return [$key => $values];
    }
}