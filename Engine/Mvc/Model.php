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
     * Default column name
     * @var string
     */
    protected $_orderExpr = null;

    /**
     * Order is asc order direction
     * @var bool
     */
    protected $_orderAsc = false;

    /**
     * Find records by array of ids
     *
     * @param array $ids
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function findByIds(array $ids)
    {
        $model = new static();
        $primary = $model->getPrimary();
        $db = $model->getWriteConnection();
        for ($i = 0; $i < count($ids); ++$i) {
            $ids[$i] = $db->escapeString($ids[$i]);
        }
        $credential = $primary." IN (".implode(",", $ids).")";

        return static::find($credential);
    }

    /**
     * Find records by array of ids
     *
     * @param string $column
     * @param array $ids
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function findByColumn($column, array $ids)
    {
        $model = new static();
        $db = $model->getWriteConnection();
        for ($i = 0; $i < count($ids); ++$i) {
            $ids[$i] = $db->escapeString($ids[$i]);
        }
        $credential = $db->escapeIdentifier($column)." IN (".implode(",", $ids).")";

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
            ? $nameExpr['function'] ."(" . implode(', ',$columns) . ")"
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
     * @param \Phalcon\DiInterface $dependencyInjection
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function queryBuilder()
    {
        $builder = new Builder();
        $builder->setModel($this);

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
        $relations = $this->getModelsManager()->getHasMany($refModel);
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
        $refModel = new $relation->getReferencedModel();
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
}