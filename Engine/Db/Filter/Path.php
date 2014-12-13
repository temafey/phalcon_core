<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use Engine\Mvc\Model\Query\Builder,
    Engine\Crud\Grid\Filter\Field\Join,
    Phalcon\Mvc\Model\Relation;

/**
 * Join path filter
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */
class Path extends AbstractFilter
{
    /**
     * Filter
     * @var \Engine\Crud\Grid\Filter\Field\Join
     */
    protected $_filterField;

    /**
     * Filter
     * @var \Engine\Db\Filter\AbstractFilter
     */
    protected $_filter;

    /**
     * Filter category model
     * @var bool|string
     */
    protected $_category;

    /**
     * Build query with all joins
     * @var bool
     */
    protected $_fullJoin = true;

    /**
     * Constructor
     *
     * @param \Engine\Crud\Grid\Filter\Field\Join $filterField
     * @param \Engine\Db\Filter\AbstractFilter $filter
     * @param string $pathCategory
     */
    public function __construct(Join $filterField, AbstractFilter $filter, $pathCategory = false)
    {
        $this->_filterField = $filterField;
        $this->_filter = $filter;
        $this->_category = $pathCategory;
    }

    /**
     * Apply filter to table select object
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @param mixed $value
     */
    public function applyFilter($dataSource)
    {
        if (!$this->_filterField->getPath()) {
            return $this->_filter->filterWhere($dataSource);
        }
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
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    public function filterWhere(Builder $dataSource)
    {
        $path = $this->_filterField->getPath();
        $dataSource->columnsJoinOne($path);
        $model = $dataSource->getModel();
        $joinPath = $model->getRelationPath($path);

        if (!$joinPath) {
            throw new \Engine\Exception("Relations to model '".get_class($model)."' by path '".implode(", ", $path)."' not valid");
        }
        if ($this->_fullJoin) {
            $dataSource->joinPath($joinPath);

            return $this->_filter->filterWhere($dataSource);
        }

        $relation = array_shift($joinPath);
        if (!$ids = $this->_processJoins($relation, $joinPath)) {
            return false;
        }
        $expr = $relation->getFields();
        $alias = $dataSource->getCorrelationName($expr);
        $this->setBoundParamKey($alias."_".$expr);

        return "(".$alias.".".$expr." IN (:".$this->getBoundParamKey().":))";
    }

    /**
     * Return bound params array
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return array
     */
    public function getBoundParams(Builder $dataSource)
    {
        if ($this->_fullJoin) {
            return $this->_filter->getBoundParams($dataSource);
        }

        $key = $this->getBoundParamKey();

        $path = $this->_filterField->getPath();
        $dataSource->columnsJoinOne($path);
        $model = $dataSource->getModel();
        $joinPath = $model->getRelationPath($path);

        if (!$joinPath) {
            throw new \Engine\Exception("Relations to model '".get_class($model)."' by path '".implode(", ", $path)."' not valid");
        }
        $relation = array_shift($joinPath);
        if (!$ids = $this->_processJoins($relation, $joinPath)) {
            return false;
        }

        return [$key => implode(",", $ids)];
    }

    /**
      Process all search query joins
     *
     * @param \Phalcon\Mvc\Model\Relation $relation
     * @param array $joinPath
     * @return array|bool
     */
    protected function _processJoins(Relation $relation, array $joinPath)
    {
        $refModel = $relation->getReferencedModel();
        $refModel = new $refModel;
        $refFields = $relation->getReferencedFields();
        $options = $relation->getOptions();

        $dataSourceIn = $refModel->queryBuilder();
        $dataSourceIn->setColumn($refFields);
        $relation = array_shift($joinPath);

        if ($joinPath) {
            if (!$ids = $this->_processJoins($relation, $joinPath)) {
                return false;
            }
            $expr = $relation->getFields();
            $where = "(".$expr." IN (".implode($ids, ",")."))";
        } else {
            //$dataSourceIn->joinPath($joinPath);
            $where = $this->_filter->filterWhere($dataSourceIn);
        }
        $dataSourceIn->andWhere($where);
        //$dataSourceIn->columnsId();
        $result = $dataSourceIn->getQuery()->execute()->toArray();

        if (count($result) == 0) {
            return false;
        }
        $ids = [];
        $adapter =  $dataSourceIn->getModel()->getReadConnection();
        foreach ($result as $row) {
            $ids[] = $adapter->escapeString($row[$refFields]);
        }

        return $ids;
    }

}