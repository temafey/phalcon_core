<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use \Engine\Mvc\Model\Query\Builder;

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
	 * Filter column
	 * @var array
	 */
	protected $_column;
	
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
	 * @param string $column
	 * @param string $value
	 * @param string $criteria
	 */
	public function __construct($column, $value, $criteria = self::CRITERIA_EQ) 
	{
		$this->_column = $column;
		$this->_value = $value;
		$this->_criteria = $criteria;
	}

	/**
	 * Return filter value string
	 *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
	 * @return string
	 */
	protected function getFilterStr(Builder $dataSource)
	{
        $compare = $this->getCompareCriteria();
		if ((strlen(floatval($this->_value)) !== strlen($this->_value)) || (strpos($this->_value, ' ') !== false)) {
            $adapter =  $dataSource->getModel()->getReadConnection();
            $this->_value = $adapter->escapeString($this->_value);
		}

		return $compare.$this->_value;
	}

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

        return $alias.".".$expr." ".$this->getFilterStr($dataSource);

	}

    /**
     * Return compare criteria
     *
     * @return string
     */
    public function getCompareCriteria()
    {
        switch ($this->_criteria) {
            case self::CRITERIA_EQ:
                $compare = " = ";
                break;
            case self::CRITERIA_NOTEQ:
                $compare = " != ";
                break;
            case self::CRITERIA_MORE:
                $compare = " >= ";
                break;
            case  self::CRITERIA_LESS:
                $compare = " <= ";
                break;
            case self::CRITERIA_MORER:
                $compare = " > ";
                break;
            case self::CRITERIA_LESSER:
                $compare = " < ";
                break;
            case self::CRITERIA_IN;
                $compare = " IN ";
                break;
            case self::CRITERIA_NOTIN:
                $compare = " < ";
                break;
            default:
                $compare = " NOT IN ";
                break;
        }

        return $compare;
    }
}