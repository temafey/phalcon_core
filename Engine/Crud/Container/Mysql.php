<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container;

use Engine\Mvc\Model,
    Engine\Mvc\Model\Query\Builder;

/**
 * Class container for Mysql
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Container
 */
abstract class Mysql extends AbstractContainer
{
    /**
     * Datasource object
     * @var \Engine\Mvc\Model\Query\Builder
     */
    protected $_dataSource = null;

    /**
     * Data source columns
     * @var array
     */
    protected $_columns = [];


    /**
     * Set model adapter
     *
     * @param string $adapter
     * @return \Engine\Crud\Container\Mysql
     */
    public function setAdapter($adapter = null)
    {
        if ($adapter) {
            $this->_adapter = $adapter;
        }
        if ($this->_model instanceof Model) {
            $this->_setModelAdapter($this->_model);
        }
        foreach ($this->_joins as $model) {
            if ($model instanceof Model) {
                $this->_setModelAdapter($model);
            }
        }

        return $this;
    }

    /**
     * Set model connection adapter
     *
     * @param \Engine\Mvc\Model $model
     */
    protected function _setModelAdapter($model)
    {
        if (is_array($this->_adapter)) {
            if (isset($this->_adapter['slave'])) {
                $model->setReadConnectionService($this->_adapter['slave']);
            }
            if (isset($this->_adapter['master'])) {
                $model->setWriteConnectionService($this->_adapter['master']);
            }
        } else {
            $model->setReadConnectionService($this->_adapter);
            $model->setWriteConnectionService($this->_adapter);
        }
    }

    /**
     * Initialize container model
     *
     * @param string $model
     * @throws \Engine\Exception
     * @return \Engine\Crud\Container\Mysql
     */
    public function setModel($model = null)
    {
        if (null === $model) {
            if (null === $this->_model) {
                throw new \Engine\Exception("Container model class not set");
            }
            $model = $this->_model;
        }
        if (is_object($model)) {
            if (!$model instanceof Model) {
                throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
            }
            $this->_setModelAdapter($model);
            $this->_model = $model;
        }
        if (is_array($model)) {
            $primaryModel = array_shift($model);
            $this->setJoinModels($model);
            $model = $primaryModel;
        } else {
            if (!empty($this->_joins)) {
                $joins = $this->_joins;
                if (!is_array($joins)) {
                    $joins = [$joins];
                }
                $this->setJoinModels($joins);
            }
        }
        if (!class_exists($model)) {
            throw new \Engine\Exception("Container model class '$model' does not exists");
        }

        $this->_model = new $model;
        if ($this->_adapter) {
            $this->_setModelAdapter($this->_model);
        } else {
            $this->_adapter = $this->_model->getWriteConnection();
        }

        return $this;
    }

    /**
     * Set join models
     *
     * @param array $models
     * @return \Engine\Crud\Container\Mysql
     */
    public function setJoinModels($models)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
            if (!is_object($model)) {
                $model = new $model;
                $this->_setModelAdapter($model);
            }
            if (!($model instanceof Model)) {
                throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
            }
            $key = $model->getSource();
            $this->_joins[$key] = $model;
        }

        return $this;
    }

    /**
     * Add join model
     *
     * @param string $model
     * @throws \Exception
     * @return string
     */
    public function addJoin($model)
    {
        if (!is_object($model)) {
            $model = new $model;
            $this->_setModelAdapter($model);
        }
        if (!($model instanceof Model)) {
            throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
        }
        $key = $model->getSource();
        if (isset($this->_joins[$key])) {
            return false;
        }
        $this->_joins[$key] = $model;

        if (null !== $this->_dataSource) {
            $this->_dataSource->columnsJoinOne($model);
        }

        return $key;
    }

    /**
     * Set column
     *
     * @param string $key
     * @param string $name
     * @param boolean $useTableAlias
     * @param boolean $useCorrelationTableName
     * @return \Engine\Crud\Container\Mysql
     */
    public function setColumn($key, $name, $useTableAlias = true, $useCorrelationTableName = false)
    {
        if (isset($this->_columns[$key])) {
            return $this;
        }
        $this->_columns[$key] = [
            $name,
            'useTableAlias' => $useTableAlias,
            'useCorrelationName' => $useCorrelationTableName
        ];
        if (null !== $this->_dataSource) {
            $this->_dataSource->setColumn($name, $key, $useTableAlias, $useCorrelationTableName);
        }

        return $this;
    }

    /**
     * Return data source object
     *
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function getDataSource()
    {
        if (null === $this->_dataSource) {
            $this->_setDataSource();
        }
        return $this->_dataSource;
    }

    /**
     * Set datasource
     *
     * @return void
     */
    abstract protected function _setDataSource();

}