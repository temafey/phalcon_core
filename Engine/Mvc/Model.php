<?php
/**
 * @namespace
 */
namespace Engine\Mvc;

use Engine\Mvc\Model\Query\Builder;

/**
 * Class Model
 *
 * @category    Engine
 * @package     Mvc
 */
class Model extends \Phalcon\Mvc\Model
{
    CONST ID = 'ID';
    CONST NAME = 'NAME';

    /**
     * Additional model constant conditions
     * @var null
     */
    protected static $_conditions = null;

    /**
     * Primary model columns
     * @var array|string
     */
    protected $_primary = null;

    /**
     * Name of column like dafault name column
     * @var string
     */
    protected $_nameExpr;

    /**
     * Model attributes (columns)
     * @var array
     */
    protected $_attributes = null;

    /**
     * Default order column
     * @var string
     */
    protected $_orderExpr = null;

    /**
     * Order is asc order direction
     * @var bool
     */
    protected $_orderAsc = false;

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param 	array $parameters
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function find($parameters=null)
    {
        if (!static::$_conditions) {
            return parent::find($parameters);
        }
        $conditions = static::normalizeConditions(static::$_conditions);
        if (!$parameters) {
            return parent::find($conditions);
        }
        if (is_string($parameters)) {
            $parameters .= " AND ".$conditions;
        } else {
            $parameters[0] .= " AND ".$conditions;
        }

        return parent::find($parameters);
    }


    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param array $parameters
     * @return \Engine\Mvc\Model
     */
    public static function findFirst($parameters=null)
    {
        if (!static::$_conditions) {
            return parent::findFirst($parameters);
        }
        $conditions = static::normalizeConditions(static::$_conditions);
        if (!$parameters) {
            return parent::findFirst($conditions);
        }
        if (is_string($parameters)) {
            $parameters .= " AND ".$conditions;
        } else {
            $parameters[0] .= " AND ".$conditions;
        }

        return parent::findFirst($parameters);
    }

    /**
     * Normalize query conditions
     *
     * @param array|string $conditions
     * @return string
     */
    public static function normalizeConditions($conditions)
    {
        if (is_string($conditions)) {
            return $conditions;
        }
        $normalizeConditions = [];
        foreach ($conditions as $key => $condition) {
            if (is_numeric($key)) {
                $normalizeConditions[] = $condition;
                continue;
            }
            if (is_array($condition)) {
                foreach ($condition as $i => $val) {
                    $condition[$i] = "'".$val."'";
                }
                $condition = $key." IN (".implode(",", $condition).")";
            } else {
                $condition = $key." = '".$condition."'";
            }
            $normalizeConditions[] = $condition;
        }

        return implode(" AND ", $normalizeConditions);
    }

    /**
     * Find records by array of ids
     *
     * @param string|array $ids
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function findByIds($ids)
    {
        $model = new static();
        $primary = $model->getPrimary();
        $db = $model->getWriteConnection();
        if (is_array($ids)) {
            for ($i = 0; $i < count($ids); ++$i) {
                if (!is_string($ids[$i])) {
                    throw new \Engine\Exception("Data type incorrect");
                }
                $ids[$i] = $db->escapeString($ids[$i]);
            }
            $credential = $primary." IN (".implode(",", $ids).")";

            return static::find($credential);
        } else {
            $credential = $primary." = ".$db->escapeString($ids);

            return static::findFirst($credential);
        }
    }

    /**
     * Find records by array of ids
     *
     * @param string $column
     * @param string|array $values
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function findByColumn($column, $values)
    {
        $model = new static();
        $db = $model->getWriteConnection();
        if (is_array($values)) {
            for ($i = 0; $i < count($values); ++$i) {
                $values[$i] = $db->escapeString($values[$i]);
            }
            $credential = $column." IN (".implode(",", $values).")";
        } else {
            $credential = $column." = ".$db->escapeString($values);
        }

        return static::find($credential);
    }

    /**
     * Return table primary key.
     *
     * @return string
     */
    public function getPrimary()
    {
        if (null === $this->_primary) {
            $this->_primary =  $this->getModelsMetaData()->getPrimaryKeyAttributes($this);
        }

        return is_array($this->_primary) ? ((isset($this->_primary[1])) ? $this->_primary[1] : $this->_primary[0]) : $this->_primary;
    }

    /**
     * Return model field name
     *
     * @return string
     */
    public function getNameExpr()
    {
        $nameExpr = (!$this->_nameExpr) ? $this->getPrimary() : $this->_nameExpr;

        if (!is_array($nameExpr)) {
            return $nameExpr;
        }

        $columns = [];
        foreach ($nameExpr['columns'] as $column) {
            $columns[] = $column;
        }
        $nameExprResult = (array_key_exists('function', $nameExpr) && !empty($nameExpr['function']))
            ? $nameExpr['function'] ."(" . implode(', ',$columns).")"
            : implode(', ',$columns);

        return $nameExprResult;
    }

    /**
     * Return model attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if (null === $this->_attributes) {
            $this->_attributes = $this->getModelsMetaData()->getAttributes($this);
        }

        return $this->_attributes;
    }

    /**
     * Return default order column
     *
     * @return string
     */
    public function getOrderExpr()
    {
        return $this->_orderExpr;
    }

    /**
     * Return default order direction
     *
     * @return string
     */
    public function getOrderAsc()
    {
        return $this->_orderAsc;
    }

    /**
     * Create a criteria for a especific model
     *
     * @param string $alias
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function queryBuilder($alias = null)
    {
        $params = [];
        $builder = new Builder($params);
        $builder->setModel($this, $alias);
        if (static::$_conditions !== null) {
            $builder->where($this->normalizeConditions(static::$_conditions));
        }

        return $builder;
    }

    /**
     * Return model relation
     *
     * @param string $refModel
     * @return \Phalcon\Mvc\Model\Relation
     */
    public function getReferenceRelation($refModel)
    {
        if (!is_object($refModel)) {
            $refName = $refModel;
            $refModel = new $refModel;
        } else {
            $refName = get_class($refModel);
        }
        if (!$refModel instanceof \Engine\Mvc\Model) {
            throw new \Engine\Exception("Model class '$refName' does not extend Engine\Mvc\Model");
        }
        $relations = $this->getModelsManager()->getBelongsTo($this);
        foreach ($relations as $relation) {
            if ($relation->getReferencedModel() == $refName) {
                return $relation;
            }
        }
        $relations = $this->getModelsManager()->getHasMany($this);
        foreach ($relations as $relation) {
            if ($relation->getReferencedModel() == $refName) {
                return $relation;
            }
        }

        return false;
    }

    /**
     * Return models relation path
     *
     * @param string|array $path
     * @return array
     */
    public function getRelationPath($path)
    {
        $relationPath = [];
        if (!$path) {
            return $relationPath;
        }
        if (!is_array($path)) {
            $path = [$path];
        }
        $rule = array_shift($path);
        if ($rule instanceof \Engine\Mvc\Model) {
            $rule = get_class($rule);
        }
        if (!$relation = $this->getReferenceRelation($rule)) {
            return $relationPath;
        }
        $relationPath[$rule] = $relation;
        if (!$path) {
            return $relationPath;
        }
        $refModel = $relation->getReferencedModel();
        $refModel = new $refModel;
        $tail = $refModel->getRelationPath($path);

        return array_merge($relationPath, $tail);
    }

    /**
     * Find reference rule and return fields.
     *
     * @param string $rule
     * @return string
     */
    public function getRelationFields($refModel)
    {
        return $this->getReferenceRelation($refModel)->getFields();
    }

    /**
     * Find reference rule and return reference fields.
     *
     * @param string $rule
     * @return string
     */
    public function getReferenceFields($refModel)
    {
        return $this->getReferenceRelation($refModel)->getReferencedFields();
    }

    /**
     * Fix field value for tinyint(1) types, from integer to string
     *
     * @return void
     */
    protected function  _preSave()
    {
        $metaData = $this->getModelsMetaData();
        $dataTypes = $metaData->getDataTypes($this);
        foreach ($dataTypes as $key => $type) {
            if ($type === \Phalcon\Db\Column::TYPE_BOOLEAN) {
                $value = $this->{$key};
                if ((int) $value === $value) {
                    $this->{$key} = (string) $value;
                }
            }
        }
    }
}