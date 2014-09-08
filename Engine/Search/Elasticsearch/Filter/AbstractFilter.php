<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use \Engine\Search\Elasticsearch\Query\Builder,
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
     * Apply filter to query builder
     *
     * @param \Engine\Search\Elasticsearch\Query\Builder $dataSource
     * @return string
     */
	abstract public function filter(Builder $dataSource);

	/**
	 * Apply filter to table select object
	 * 
	 * @param \Engine\Search\Elasticsearch\Query\Builder $dataSource
	 * @param mixed $value
	 */
	public function applyFilter($dataSource)
	{
		$condition = $this->filter($dataSource);
		if ($condition) {
			$dataSource->apply($condition);
		}
	}

}
