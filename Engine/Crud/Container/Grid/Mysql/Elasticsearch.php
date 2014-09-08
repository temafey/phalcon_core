<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container\Grid\Mysql;

use Engine\Crud\Container\Grid\Mysql as Container,
    Engine\Crud\Container\Grid\Adapter as GridContainer,
    Engine\Search\Elasticsearch\Query\Builder,
    Engine\Search\Elasticsearch\Type,
    Engine\Search\Elasticsearch\Query;

/**
 * Class container for MySql with using ElasticSearch filters
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Container
 */
class Elasticsearch extends Container implements GridContainer
{
    /**
     * @var \Engine\Search\Elasticsearch\Query\Builder
     */
    protected $_elasticDataSource;

    /**
     * @var \Engine\Search\Elasticsearch\Type
     */
    protected $_elasticType;

    /**
     * Return data array
     *
     * @return array
     */
    public function getData($dataSource)
    {
        $limit = $this->_grid->getLimit();
        //$extraLimit = $this->_grid->getExtraLimit();
        $extraLimit = 100;
        $page = $this->_grid->getPage();
        $extraPage = (int) ceil(($limit*$page)/$extraLimit);
        $extraOffset = ($extraPage - 1)*$extraLimit;
        $paginator = $this->_getPaginator($this->getElasticDataSource(), $extraLimit, $extraOffset);

        $items = [];
        $position = $limit*($page-1)-($extraLimit*($extraPage-1));
        $results = $this->_elasticType->search($paginator);
        $total = $results->getTotalHits();
        $pages = (int) ceil($total/$limit);
        if ($total > 0) {
            $pageTotal = ($position+$limit < $total) ? ($position+$limit) : $total;
            for ($i = $position; $i < $pageTotal; ++$i) {
                $item = $results[$i]->getData();
                $items[] = $item['id'];
            }
        }
        $data = [
            'data' => $this->_getData($dataSource, $items),
            'page' => $page,
            'limit' => $limit,
            'mess_now' => count($items)
        ];

        if ($this->_grid->isCountQuery()) {
            $data['pages'] = $pages;
            $data['lines'] = $total;
        }

        return $data;
    }

    /**
     * Return data source object
     *
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function getElasticDataSource()
    {
        if (null === $this->_elasticDataSource) {
            $this->_setElasticDataSource();
        }
        return $this->_elasticDataSource;
    }

    /**
     * Set datasource
     *
     * @return void
     */
    protected function _setElasticDataSource()
    {
        $this->_elasticDataSource = new Builder();
        $this->_elasticDataSource->setModel($this->_model);

        $this->_elasticType = new Type($this->_model->getSource());
        $this->_elasticType->setDi($this->getDi());
        $this->_elasticType->setEventsManager($this->getEventsManager());
    }

    /**
     * Return filter object
     *
     * @return \Engine\Filter\SearchFilterInterface
     */
    public function getFilter()
    {
        $args = func_get_args();
        $type = array_shift($args);
        $className = $this->getFilterClass($type);
        $rc = new \ReflectionClass($className);
        $filter = $rc->newInstanceArgs($args);
        $filter->setDi($this->getDi());

        return $filter;
    }

    /**
     * Return filter class name
     *
     * @param string $type
     * @return string
     */
    public function getFilterClass($type)
    {
        return '\Engine\Search\Elasticsearch\Filter\\'.ucfirst($type);
    }

    /**
     * Setup paginator.
     *
     * @param \Engine\Search\Elasticsearch\Query\Builder $queryBuilder
     * @return \ArrayObject
     */
    protected function _getPaginator($queryBuilder, $limit, $offset)
    {
        $query = new Query();
        $query->setQuery($queryBuilder->getQuery());
        $query->setPostFilter($queryBuilder->getFilter());

        $sort = $this->_grid->getSortKey();
        if (null === $sort) {
            $sort = $this->_model->getOrderExpr();
        }
        if (!$filterField = $this->_grid->getFilter()->getFieldByKey($sort)) {
            if (!$filterField = $this->_grid->getFilter()->getFieldByName($sort)) {
                throw new \Engine\Exception("Can't sort by '".$sort."' column, didn't find this field in search index");
            }
        }
        $sort = $filterField->getName();
        $direction = $this->_grid->getSortDirection();
        if (null === $direction) {
            $direction = ($this->_model->getOrderAsc()) ? "asc" : "desc";
        }
        if ($sort) {
            if ($direction) {
                $query->setSort([$sort.".sort" => ['order' => $direction]]);
            } else {
                $query->setSort($sort.".sort");
            }
        }

        $query->setSize($limit);
        $query->setFrom($offset);

        return $query;
    }

    /**
     * Get data from grid mysql datasource by primary field
     *
     * @param \Engine\Mvc\Model\Query\Builder $datasource
     * @param array $ids
     * @return array
     */
    protected function _getData($datasource, array $ids)
    {
        if (!$ids) {
            return false;
        }
        $source = $datasource->getModel()->getSource();
        $primayField = $datasource->getModel()->getPrimary();

        return $datasource->andWhere($source.".".$primayField." IN (".implode(", ", $ids).")")->getQuery()->execute();
    }
}