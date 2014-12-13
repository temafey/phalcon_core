<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch;

use Elastica\Query as ElQuery,
    Elastica\Filter\AbstractFilter,
    Elastica\Query\AbstractQuery;

/**
 * Class Builder
 *
 * @category    Engine
 * @package     Search
 * @subcategory Elasticsearch
 */
class Query extends ElQuery
{

    /**
     * Sets the query
     *
     * @param  \Elastica\Query\AbstractQuery $query Query object
     * @return \Elastica\Query               Query object
     */
    public function setQuery(AbstractQuery $query)
    {
        if ($query instanceof AbstractQuery) {
            $query = $this->normalizeParams($query->toArray());
            if (!$query) {
                $query = ["bool" => ["must" => ["match_all" => []]]];
            }
        }

        return $this->setParam('query', $query);
    }

    /**
     * Sets post_filter argument for the query. The filter is applied after the query has executed
     *
     * @param   array|\Elastica\Filter\AbstractFilter $filter
     * @return  \Elastica\Param
     * @link    http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-post-filter.html
     */
    public function setPostFilter($filter)
    {
        if ($filter instanceof AbstractFilter) {
            $filter = $this->normalizeParams($filter->toArray());
        } else {
            trigger_error('Deprecated: Elastica\Query::setPostFilter() passing filter as array is deprecated. Pass instance of AbstractFilter instead.', E_USER_DEPRECATED);
        }

        return $this->setParam("post_filter", $filter);
    }

    /**
     * Normalize filter array, remove all epmty values
     *
     * @param array $filters
     * @return array
     */
    public function normalizeParams(array $params)
    {
        foreach ($params as $key => $value) {
            if (is_object($value)) {
                $value = $value->toArray();
            }
            if (is_array(($value))) {
                $value = $this->normalizeParams($value);
            }
            if ($value === false || $value === "" || (is_array($value) && count($value) == 0)) {
                unset($params[$key]);
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}