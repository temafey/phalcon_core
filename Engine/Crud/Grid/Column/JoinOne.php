<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\Column,
    Engine\Mvc\Model;

/**
 * Join one column
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class JoinOne extends Collection
{
    /**
     * Field type.
     * @var string
     */
    protected $_type = 'collection';

	/**
	 * Join path
	 * @var array|string
	 */
	protected $_path;
	
	/**
	 * Join column
	 * @var string
	 */
	protected $_column;
	
	/**
	 * Else join columns
	 * @var array
	 */
	protected $_columns;
	
	/**
	 * No value
	 * @var string
	 */
	protected $_na = "---";

    /**
     * Not use joins in models
     * @var bool
     */
    protected $_noJoin = false;

    /**
     * Query builder for get value from referenced model
     * @var \Engine\Mvc\Model\Query\Builder
     */
    protected $_joinQueryBuilder;

    /**
     * Join field name
     * @var string
     */
    protected $_joinField;

	/**
	 * Constructor
	 * 
	 * @param string $title
	 * @param string|array $path
	 * @param string $column
	 * @param array $columns
	 * @param bool $hidden
	 * @param int $width
	 */
	public function __construct(
        $title,
        $path,
        $column = null,
        $columns = null,
        $isSortable = true,
        $isHidden = false,
        $width = 200,
        $extraOptions = [],
        $isEditable = true,
        $fieldKey = null,
        $na = "---"
    ) {
		parent::__construct($title, $column, [], $isSortable, $isHidden, $width, $isEditable, $fieldKey);
		
		$this->_path = $path;
		$this->_column = $column;
		$this->_columns = $columns;
        $this->_extraOptions = $extraOptions;
        $this->_na = $na;
	}

    /**
     * Update grid container
     *
     * @param \Engine\Crud\Container\Grid\Adapter $container
     * @return \Engine\Crud\Grid\Column
     */
    public function updateContainer(\Engine\Crud\Container\Grid\Adapter $container)
    {
        //$container->setField($this->_key, $this->_name);
        return $this;
    }


	/**
	 * Update container data source
	 * 
	 * @param \Engine\Crud\Container\Grid\Adapter $dataSource
	 * @return \Engine\Crud\Grid\Column\JoinOne
	 */
	public function updateDataSource($dataSource)
	{
        if ($this->_noJoin) {
            $relation = $this->_getJoinRelation();
            $joinField = $relation->getFields();
            $dataSource->setColumn($joinField, $joinField);
            return $this;
        }
        if (null === $this->_column) {
            $this->_column = \Engine\Mvc\Model::NAME;
        }
		$columns =  [
            $this->_key => $this->_column,
            $this->_key.'_'.Model::JOIN_PRIMARY_KEY_PREFIX => \Engine\Mvc\Model::ID
        ];
		if (!empty($this->_columns)) {
		    $columns = (is_array($this->_columns)) ? array_merge($columns, $this->_columns) : array_merge($columns, [$this->_columns => $this->_columns]);
		}
        $dataSource->columnsJoinOne($this->_path, $columns);

		return $this;
	}

    /**
     * Return render value
     *
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column::render()
     * @param mixed $row
     * @return string
     */
    public function render($row)
    {
        if ($this->_noJoin) {
            $value = $this->_getValue($row);
        } else {
            if (!property_exists($row, $this->_key)) {
                return $this->_na;
            }
            $value = $row->{$this->_key};
        }
		if (array_key_exists($value, $this->_extraOptions)) {
            $value = $this->_extraOptions[$value];
        }

		return $value ? $this->filter($value) : $this->_na;
	}

    /**
     * Return column value by key
     *
     * @param mixed $row
     * @return string|integer
     */
    public function getValue($row)
    {
        if ($this->_noJoin) {
            $value = $this->_getValue($row);
        } else {
            $value = $row->{$this->_key};
        }
        $value = $this->filter($value);

        return $value;
    }

    /**
     * Set null value
     *
     * @param string $na
     * @return \Engine\Crud\Grid\Column\JoinOne
     */
    public function setNullValue($na)
    {
        $this->_na = $na;
        return $this;
    }

    /**
     * Set null value
     *
     * @param string $na
     * @return \Engine\Crud\Grid\Column\JoinOne
     */
    public function setNoJoin()
    {
        $this->_noJoin = true;
        $this->_isSortable = false;
        return $this;
    }

    /**
     * Is column using join between models
     *
     * @return bool
     */
    public function isUseJoin()
    {
        return !$this->_noJoin;
    }

    /**
     * Return values
     *
     * @param object $row
     * @return array
     */
    protected function _getValue($row)
    {
        if (!$this->_joinQueryBuilder) {
            $path = $this->_path;
            if (!is_array($path)) {
                $path = [$path];
            }
            $joinModel = array_shift($path);
            $model = new $joinModel;
            $modelAdapter = $this->_grid->getModelAdapter();
            if ($modelAdapter) {
                $model->setConnectionService($modelAdapter);
            }
            $this->_joinQueryBuilder = $model->queryBuilder();
            if (!$path) {
                $this->_joinQueryBuilder->columnsJoinOne($path);
            }
            $columns =  [
                $this->_key => $this->_column,
                $this->_key.'_'.Model::JOIN_PRIMARY_KEY_PREFIX => $model->getPrimary()
            ];
            if (!empty($this->_columns)) {
                $columns = (is_array($this->_columns)) ? array_merge($columns, $this->_columns) : array_merge($columns, [$this->_columns => $this->_columns]);
            }
            $this->_joinQueryBuilder->columns($columns);

            $relation = $this->_getJoinRelation();
            $feferencedField = $relation->getReferencedFields();
            $this->_joinField = $relation->getFields();
            $this->_joinQueryBuilder->andWhere($feferencedField." = :id:");
        }
        $id = $row->{$this->_joinField};
        $rows = $this->_joinQueryBuilder->getQuery()->execute(['id' => $id])->toArray();

        if (!$rows) {
            return null;
        }
        $value = $rows[0][$this->_key];

        return $value;
    }

    /**
     * Return models relation object
     *
     * @return \Phalcon\Mvc\Model\Relation
     * @throws \Engine\Exception
     */
    protected function _getJoinRelation()
    {
        $path = $this->_path;
        if (!is_array($path)) {
            $path = [$path];
        }
        $joinModel = array_shift($path);
        $mainModel = $this->_grid->getContainer()->getDataSource()->getModel();
        $relations = $mainModel->getRelationPath($joinModel);
        if (!$relations) {
            throw new \Engine\Exception("Relations to model '".$joinModel."' by path '".implode(", ", $this->_path)."' not valid");
        }

        return array_pop($relations);
    }
}