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

    CONST VALUE_TYPE_INT    = 'integer';
    CONST VALUE_TYPE_DOUBLE = 'double';
    CONST VALUE_TYPE_STRING = 'string';
    CONST VALUE_TYPE_ARRAY  = 'array';
    CONST VALUE_TYPE_DATE   = 'date';
    CONST VALUE_TYPE_GEO    = 'geo';

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
