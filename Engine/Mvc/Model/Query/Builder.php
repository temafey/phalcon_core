<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Model\Query;

use Phalcon\Mvc\Model\Query\Builder as PhBuilder;

/**
 * Class Builder
 *
 * @category    Engine
 * @package     Mvc
 * @subcategory Model
 */
class Builder extends PhBuilder
{
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
     * Set column to query
     *
     * @param string $column
     * @param string $alias
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function setColumn($column, $alias = null)
    {
        if ($alias == $column || is_numeric($alias)) {
            $alias = null;
        } elseif ($alias === false) {
            $this->_columns[] = $column;
            return $this;
        }
        $model = $this->getAlias();
        if (null === $alias) {
            $this->_columns[] = $model.".".$column;
        } else {
            $this->_columns[$alias] = $model.".".$column;
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
                parent::columns($columns);
                return $this;
            }
            $columns = [$columns];
        }
        $this->_columns = [];
        foreach ($columns as $alias => $column) {
            $this->setColumn($column, $alias);
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
     * Join all models
     *
     * @param  array $joinPath
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function joinPath(array $joinPath, $columns = null)
    {
        $model = $this->getAlias();
        foreach ($joinPath as $rule => $relation) {
            $fields = $relation->getFields();
            $refModel = $relation->getReferencedModel();
            $refFields = $relation->getReferencedFields();
            $options = $relation->getOptions();
            $alias = (isset($options['alias'])) ? $options['alias'] : $refModel;
            if ($this->_joins) {
                foreach ($this->_joins as $join) {
                    if ($join[2] == $alias) {
                        return $this;
                    }
                }
            }
            $this->leftJoin($refModel, $model.'.'.$fields.' = '.$alias.'.'.$refFields, $alias);
            $this->joinColumns($columns, new $refModel, $alias);
            $model = $alias;
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
                $this->_columns[$name] = $alias.".".$column;
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

        return current($correlationNameKeys);
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
            foreach ($order as $i => $key){
                $direction[$i] = ($direction[$i] ^ $reverse) ? "ASC" : "DESC";
                $alias = $this->getCorrelationName($key);
                $orderPre[] = $alias.".".$key." ".$direction[$i];
            }
            $this->orderBy(implode(",", $orderPre));
        }

        return $this;
    }
}