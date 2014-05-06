<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use Engine\Mvc\Model\Query\Builder,
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
     * Join path
     * @var string|array
     */
    protected $_path;

    /**
     * Filter
     * @var \Engine\Db\Filter\AbstractFilter
     */
    protected $_filter;

    /**
     * Build query with all joins
     * @var bool
     */
    protected $_fullJoin = true;

    /**
     * Constructor
     *
     * @param array $path
     * @param \Engine\Db\Filter\AbstractFilter $filter
     */
    public function __construct($path, AbstractFilter $filter)
    {
        $this->_path = $path;
        $this->_filter = $filter;
    }

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    public function filterWhere(Builder $dataSource)
    {
        if (!$this->_path) {
            return $this->_filter->filterWhere($dataSource);
        }
        $model = $dataSource->getModel();
        $joinPath = $model->getRelationPath($this->_path);

        if ($joinPath) {
            if ($this->_fullJoin) {
                $dataSource->joinPath($joinPath);
            } else {
                $relation = array_shift($joinPath);
                if (!$ids = $this->_processJoins($relation, $joinPath)) {
                    return false;
                }
                $fields = $relation->getFields();
                $alias = $dataSource->getCorrelationName($fields);

                return "(".$alias.".".$fields." IN (".implode($ids, ",")."))";
            }
        }

        return $this->_filter->filterWhere($dataSource);
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
            $fields = $relation->getFields();
            $where = "(".$fields." IN (".implode($ids, ",")."))";
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