<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use \Engine\Mvc\Model\Query\Builder,
    \Engine\Filter\SearchFilterInterface,
    \Phalcon\Events\EventsAwareInterface,
    \Phalcon\DI\InjectionAwareInterface;

/**
 * Class database filters
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */ 
abstract class AbstractFilter implements SearchFilterInterface, EventsAwareInterface, InjectionAwareInterface
{
    use \Engine\Tools\Traits\DIaware,
        \Engine\Tools\Traits\EventsAware;

    /**
     * Key for bound param
     * @var string
     */
    protected $_boundParamKey;

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
	abstract public function filterWhere(Builder $dataSource);

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    abstract public function getBoundParams(Builder $dataSource);

	/**
	 * Apply filter to table select object
	 * 
	 * @param \Engine\Mvc\Model\Query\Builder $dataSource
	 * @param mixed $value
	 */
	public function applyFilter($dataSource)
	{
        $where = $this->filterWhere($dataSource);
        if (!$where) {
            return false;
        }
        $params = $this->getBoundParams($dataSource);
        if ($params === false) {
            return false;
        }
        $dataSource->andWhere($where, $params);
	}

    /**
     * Set key for bound param value
     *
     * @param string $key
     * @return \Engine\Db\Filter\AbstractFilter
     */
    public function setBoundParamKey($key)
    {
        $this->_boundParamKey = $key;
        return $this;
    }

    /**
     * Return key for bound param value
     *
     * @return string
     */
    public function getBoundParamKey()
    {
        return $this->_boundParamKey;
    }

    /**
     * Return compare criteria
     *
     * @param string $criteria
     * @param mixed $value
     * @return string
     */
    public function getCompareCriteria($criteria, $value)
    {
        if (null == $value) {
            if ($criteria == self::CRITERIA_NOTEQ || $criteria == self::CRITERIA_NOTIN) {
                $compare = "IS NOT";
            } else {
                $compare = "IS";
            }
        } else {
            switch ($criteria) {
                case self::CRITERIA_EQ:
                    $compare = "=";
                    break;
                case self::CRITERIA_NOTEQ:
                    $compare = "!=";
                    break;
                case self::CRITERIA_MORE:
                    $compare = ">=";
                    break;
                case  self::CRITERIA_LESS:
                    $compare = "<=";
                    break;
                case self::CRITERIA_MORER:
                    $compare = ">";
                    break;
                case self::CRITERIA_LESSER:
                    $compare = "<";
                    break;
                case  self::CRITERIA_LIKE:
                    $compare = "LIKE";
                    break;
                case self::CRITERIA_BEGINS:
                    $compare = "LIKE";
                    break;
                case self::CRITERIA_IN;
                    $compare = "IN";
                    break;
                case self::CRITERIA_NOTIN:
                    $compare = "<";
                    break;
                default:
                    $compare = "NOT IN";
                    break;
            }
        }

        return $compare;
    }

}
