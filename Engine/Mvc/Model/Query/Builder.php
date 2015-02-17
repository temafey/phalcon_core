<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Model\Query;

use Phalcon\Mvc\Model\Query\Builder as PhBuilder,
    Engine\Mvc\Model\Query as EnQuery;

/**
 * Class Builder
 *
 * @category    Engine
 * @package     Mvc
 * @subcategory Model
 */
class Builder extends PhBuilder
{
    const DISTINCT       = 'distinct';
    const COLUMNS        = 'columns';
    const FROM           = 'from';
    const UNION          = 'union';
    const WHERE          = 'where';
    const GROUP          = 'group';
    const HAVING         = 'having';
    const ORDER          = 'order';
    const LIMIT_COUNT    = 'limitcount';
    const LIMIT_OFFSET   = 'limitoffset';
    const FOR_UPDATE     = 'forupdate';

    const COUNT = "COUNT(*)";
    const SEPARATOR = "\n";
    const ALIAS = "t";

    /**
     * @var \Engine\Mvc\Model
     */
    protected $_model;

    /**
     * Set model
     *
     * @param \Engine\Mvc\Model $model
     * @param string $alias
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function setModel(\Engine\Mvc\Model $model, $alias = null)
    {
        $this->_model = $model;
        if (!$alias) {
           $alias = $model->getSource();
        }
        $this->addFrom(get_class($model), $alias);

        return $this;
    }

    /**
     * Return model object
     *
     * @return \Engine\Mvc\Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Return table alias
     *
     * @return string
     */
    public function getAlias()
    {
        $from = $this->getFrom();
        $key = key($from);
        return ($key) ? $key : current($from);
    }

    /**
     * Return alias from joined table
     *
     * @param string|\Engine\Mvc\Model $model
     * @return string
     */
    public function getJoinAlias($model)
    {
        if ($model instanceof \Engine\Mvc\Model) {
            $model = get_class($model);
        } elseif (is_string($model) and !class_exists($model)) {
            throw new \Engine\Exception("'{$model}' class not exists!");
        }
        $model = trim($model, "\\");
        foreach ($this->_joins as $join) {
            $joinModel = trim($join[0], "\\");
            if ($model == $joinModel) {
                return $join[2];
            }
        }

        return false;
    }

    /**
     * Set column to query
     *
     * @param string $column
     * @param string $alias
     * @param boolean $useTableAlias
     * @param boolean $useCorrelationName
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function setColumn($column, $alias = null, $useTableAlias = true, $useCorrelationName = false)
    {
        if (!is_string($column)) {
            throw new \Engine\Exception("Column value should be only string data type");
        }
        if ($alias == $column || is_numeric($alias)) {
            $alias = null;
        } elseif ($alias === false) {
            $this->_columns[] = $column;
            return $this;
        }

        if ($useTableAlias) {
            if ($useCorrelationName) {
                $column = $this->getCorrelationName($column).".".$column;
            } else {
                $column = $this->getAlias().".".$column;
            }
        }

        if ($alias) {
            $this->_columns[$alias] = $column;
        } else {
            $this->_columns[] = $column;
        }

        return $this;
    }

    /**
     * Include primary key to Query condition.
     *
     * @param string $alias
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function columnsId($alias = "id")
    {
        $primary = $this->_model->getPrimary();
        $this->setColumn($primary, $alias);

        return $this;
    }

    /**
     * Include column with name alias to Query condition.
     *
     * @param string $alias
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function columnsName($alias = "name")
    {
        $name = $this->_model->getNameExpr();
        $this->setColumn($name, $alias);

        return $this;
    }

    /**
     * Sets the columns to be queried
     *
     * @param string|array $columns
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function columns($columns)
    {
        if (!$columns) {
            return $this;
        }
        if (is_string($columns)) {
            if (strpos(strtolower($columns), "rowcount") !== false) {
                parent::columns([$columns]);
                return $this;
            }
            $columns = [$columns];
        }
        $this->_columns = [];
        foreach ($columns as $alias => $column) {
            if (is_array($column)) {
                $useTableAlias = (isset($column['useTableAlias'])) ? $column['useTableAlias'] : true;
                $useCorrelationName = (isset($column['useCorrelationName'])) ? $column['useCorrelationName'] : false;
                $this->setColumn($column[0], $alias, $useTableAlias, $useCorrelationName);
            } else {
                $this->setColumn($column, $alias);
            }
        }

        return $this;
    }

    /**
     * Check for existing join rules and set join between table.
     *
     * @param array|string $path
     * @param array|string $columns
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function columnsJoinOne($path, $columns = null)
    {
        if (!$this->_model) {
            throw new \Engine\Exception("Model class not set");
        }
        $relationPath = $this->_model->getRelationPath($path);
        if (is_array($relationPath)) {
            $this->joinPath($relationPath, $columns);
        }

        return $this;
    }

    /**
     * Set field to current table by many to many rule path.
     *
     * @param string|array $path
     * @param string $fieldAlias
     * @param string $tableField
     * @param string $orderBy
     * @param string $separator
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function columnsJoinMany($path, $fieldAlias = null, $tableField = null, $orderBy = null, $separator = null)
    {
        if (!$path) {
            throw new \Engine\Exception("Non empty path is required, model '".get_class($this->_model)."'");
        }
        if (!is_array($path)) {
            $path = [$path];
        }

        $relationPath = $this->_model->getRelationPath($path);
        $this->joinPath($relationPath);
        $this->groupBy($this->getAlias().".".$this->_model->getPrimary());

        $prevRef = array_pop($relationPath);
        $refModel = trim($prevRef->getReferencedModel(). "\\");
        $refOptions = $prevRef->getOptions();
        $refAlias = (isset($refOptions['alias'])) ? $refOptions['alias'] : $refModel;
        if ($fieldAlias == null) {
            $fieldAlias = $refAlias;
        }
        $refModel = new $refModel;
        $adapter = $this->_model->getReadConnectionService();
        $refModel->setConnectionService($adapter);
        $field = ($tableField !== null) ? $tableField : $refModel->getNameExpr();

        if ($separator === null) {
            $separator = self::SEPARATOR;
        }
        if ($tableField == self::COUNT) {
            $this->setColumn("COUNT({$refAlias}.{$refModel->getPrimary()})", $fieldAlias, false);
        } else {
            if (!$orderBy) {
                $orderBy = $refModel->getOrderExpr();
            }
            $this->setColumn("(LEFT(GROUP_CONCAT($refAlias.$field ORDER BY $refAlias.$orderBy SEPARATOR '$separator'), 250))", $fieldAlias, false);
        }

        return $this;
    }

    /*public function columnsJoinMany($path, $fieldAlias = null, $tableField = null, $orderBy = null, $separator = null)
    {
        if (!$path) {
            throw new \Engine\Exception("Non empty path is required, model '".get_class($this->_model)."'");
        }
        if (!is_array($path)) {
            $path = [$path];
        }
        $relationPath = $this->_model->getRelationPath($path);
        $relationPath = array_reverse($relationPath);
        $prevRef = array_shift($relationPath);
        $refModel = $prevRef->getReferencedModel();
        $refModel = new $refModel;
        $adapter = $this->_model->getReadConnectionService();
        $refModel->setConnectionService($adapter);
        $query = $refModel->queryBuilder("m");
        $field = ($tableField !== null) ? $tableField : $refModel->getNameExpr();

        if ($separator === null) {
            $separator = self::SEPARATOR;
        }
        //$query->reset(self::COLUMNS);
        if ($tableField === self::COUNT) {
            $query->setColumn("COUNT(*)", 'c', false);
        } else {
            $alias = $query->getAlias();
            if ($orderBy) {
                $query->setColumn("LEFT(GROUP_CONCAT($alias.$field ORDER BY $orderBy SEPARATOR '$separator'), 250)", 'c', false);
            } else {
                $query->setColumn("LEFT(GROUP_CONCAT($alias.$field SEPARATOR '$separator'), 250)", 'c', false);
            }
        }

        $refPath = array_slice(array_reverse($path), 1);
        $alias = $query->getAlias();
        if ($refPath) {
            $joinPathRev = $refModel->getRelationPath($refPath);
            $query->joinPath($joinPathRev);
            $prevRef = array_pop($joinPathRev);
            $alias = $prevRef->getReferencedModel();
        }
        $query->andWhere($alias.".".$prevRef->getReferencedFields()." = ".$this->getAlias().".".$prevRef->getFields());

        if ($fieldAlias == null) {
            $fieldAlias = get_class($refModel);
        }

        $this->setColumn($query, $fieldAlias, false);

        return $this;
    }*/

    /**
     * Join all models
     *
     * @param  array $joinPath
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function joinPath(array $joinPath, $columns = null)
    {
        $model = $this->getAlias();
        $joinColumns = false;
        $alias = false;
        $adapter = $this->_model->getReadConnectionService();
        foreach ($joinPath as $rule => $relation) {
            $joinColumns = true;
            $fields = $relation->getFields();
            $refModel = trim($relation->getReferencedModel(), "\\");
            $refFields = $relation->getReferencedFields();
            $options = $relation->getOptions();
            $alias = (isset($options['alias'])) ? $options['alias'] : $refModel;
            if ($this->_joins) {
                foreach ($this->_joins as $join) {
                    if ($join[2] == $alias) {
                        continue 2;
                    }
                }
            }
            $this->leftJoin($refModel, $model.'.'.$fields.' = '.$alias.'.'.$refFields, $alias);
            $model = $alias;
        }
        if ($joinColumns) {
            $refModel = new $refModel;
            $refModel->setConnectionService($adapter);
            $this->joinColumns($columns, $refModel, $alias);
        }

        return $this;
    }

    /**
     * Join columns
     *
     * @param array $columns
     * @param string $refModel
     * @param string $alias
     */
    public function joinColumns($columns, $refModel, $alias)
    {
        if (!$columns) {
            return;
        }
        if (is_string($columns) && $columns != '*') {
            $columns = [$columns => null];
        }
        if (is_array($columns)) {
            foreach ($columns as $name => $column) {
                if (!$column || $column === \Engine\Mvc\Model::NAME) {
                    $column = $refModel->getNameExpr();
                } elseif ($column === \Engine\Mvc\Model::ID) {
                    $column = $refModel->getPrimary();
                }
                $column = $alias.".".$column;
                if (isset($this->_columns[$name]) && $this->_columns[$name] !== $column) {
                    throw new \Engine\Exception("Column with alias '".$name."' already exists in column list, exists column '".$this->_columns[$name]."', new column '".$column."'");
                }
                $this->_columns[$name] = $column;
            }
        }
    }

    /**
     * Return table name by column name
     *
     * @param $col
     * @return string
     */
    public function getCorrelationName($col)
    {
        $correlationNameKeys = $this->getFrom();
        if ($col == '*') {
            return $this->getAlias();
        }
        foreach ($correlationNameKeys as $key => $modelName) {
            $model = new $modelName;
            $cols = $model->getAttributes();
            if (in_array($col, $cols)) {
                return (is_numeric($key) ? $modelName : $key);
            }
        }

        $correlationNameKeys = $this->_joins;
        if (!$correlationNameKeys) {
            return $this->getAlias();
        }
        foreach ($correlationNameKeys as $modelName) {
            $model = new $modelName[0];
            $cols = $model->getAttributes();
            if (in_array($col, $cols)) {
                return $modelName[2];
            }
        }

        return $this->getAlias();
    }

    /**
     * Set model default order to query
     *
     * @param bool $reverse
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function orderNatural($reverse = false)
    {
        $direction = $this->_model->getOrderAsc();
        if ($order = $this->_model->getOrderExpr()) {
            if (!is_array($order)) {
                $order = [$order];
            }
            if (!is_array($direction)) {
                $direction = [$direction];
            }
            $orderPre = [];
            foreach ($order as $i => $key) {
                $direction[$i] = ($direction[$i] ^ $reverse) ? "ASC" : "DESC";
                $alias = $this->getCorrelationName($key);
                $orderPre[] = $alias.".".$key." ".$direction[$i];
            }
            $this->orderBy(implode(",", $orderPre));
        }

        return $this;
    }

    /**
     * Clear parts of the Query object, or an individual part.
     *
     * @param string $part OPTIONAL
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function reset($part = null)
    {
        if ($part == null) {
            $this->_columns = [];
        } else {
            switch ($part) {
                case self::COLUMNS:
                    $this->_columns = [];
                    break;
                case self::LIMIT_COUNT:
                    $this->_limit = null;
                    break;
                case self::LIMIT_OFFSET:
                    $this->_offset = null;
                    break;
                case self::ORDER:
                    $this->_order = null;
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns the query built
     *
     * @return \Engine\Mvc\Model\Query
     */
    public function getQuery()
    {
        $query = new EnQuery($this->getPhql());
        $query->setDI($this->getDI());
        if ($this->_bindParams) {
            $query->setBindParams($this->_bindParams);
        }
        if ($this->_bindTypes) {
            $query->setBindTypes($this->_bindTypes);
        }
        //$query->setType();
        return $query;
    }
}