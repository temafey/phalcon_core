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
        if (!$params = $this->getBoundParams($dataSource)) {
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

}
