<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\Column;

/**
 * Join many column
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class JoinMany extends Column
{
	/**
	 * Join path
	 * @var string|array
	 */
	protected $_path;
	
	/**
	 * Join column
	 * @var string
	 */
	protected $_column;
	
	/**
	 * Join table order
	 * @var string
	 */
	protected $_order;
	
	/**
	 * Value separator
	 * @var string
	 */
	protected $_separator;
	
	/**
	 * Count of join rows
	 * @var integer
	 */
	protected $_count;

    /**
     * Load values by separate queries
     *
     * @var bool
     */
    protected $_separateQueries = true;

    /**
     * @var \Engine\Mvc\Model\Query\Builder
     */
    protected $_queryBuilder;
	
	/**
	 * No value
	 * @var string
	 */
	protected $_na = "---";
	
	protected $_left = null;
	protected $_right = null;
	protected $_tag = null;

	/**
	 * Constructor
	 * 
	 * @param string $title
	 * @param string|array $path
	 * @param string $column
	 * @param string $orderBy
	 * @param string $separator
	 * @param int $count
	 * @param int $width
	 */
	public function __construct(
        $title,
        $path = null,
        $column = null,
        $orderBy = null,
        $separator = ',',
        $count = 5,
        $width = 450,
        $isEditable = true
    ) {
		parent::__construct($title, null, false, false, $width, $isEditable, null);
		
		$this->_path = $path;
		$this->_column = $column;
		$this->_orderBy = $orderBy;
		$this->_separator = $separator;
		$this->_count = $count;
	}
	
	/**
	 * Update container data source
	 * 
	 * @param \Engine\Crud\Container\Grid\Adapter $dataSource
	 * @return \Engine\Crud\Grid\Column\JoinMany
	 */
	public function updateDataSource($dataSource)
	{
        if (!$this->_separateQueries) {
		    $dataSource->columnsJoinMany($this->_path, $this->_key, $this->_column, $this->_orderBy, $this->_separator);
        }

		return $this;
	}

    /**
     * Update grid container
     *
     * @param \Engine\Crud\Container\Grid\Adapter $container
     * @return \Engine\Crud\Grid\Column
     */
    public function updateContainer(\Engine\Crud\Container\Grid\Adapter $container)
    {
        //$container->setColumn($this->_key, $this->_name);
        return $this;
    }

    /**
     * Return render value
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column::render()
     * @param mixed $row
     * @return string
     */
	public function render($row)
	{
        if ($this->_separateQueries) {
            $values = $this->_getManyValues($row['id']);
            $count = count($values);
        } else {
            $value = $row[$this->_key];
            $values = explode($this->_separator, $value);
            $count = count($values);
            if (($this->_count !== false) && ($this->_count !== null)) {
                $values = array_slice($values, 0, $this->_count);
            }
        }

		if (null !== $this->_tag) {
		    foreach ($values as $i => $val) {
		        if ($this->_tag == '<b>' || $this->_tag == 'b') {
		            $values = "<b>".$val ."</b>";
		        } elseif ($this->_tag == '<strong>' || $this->_tag == 'strong') {
		            $values[$i] = "<strong>".$val ."</strong>";
		        } elseif ($this->_tag == '<li>' || $this->_tag == 'li') {
		            $values[$i] = "<li>".$val ."</li>";
		        }
		    }
		}
		
		$value = implode($this->_separator, $values);
		if (($this->_count !== false) && ($this->_count !== null) && $count > $this->_count) {
			$value .= $this->_separator."...";
		}
		if ($count == 0) {
			 $value = $this->_na;
		}
		if (!empty($this->_left)) {
		    $value = $this->_left.$value;
		}
	    if (!empty($this->_right)) {
		    $value .= $this->_right;
		}

		return $value;
	}

    /**
     * Return values
     *
     * @param string $id
     * @return array
     */
    protected function _getManyValues($id)
    {
        $name = \Engine\Mvc\Model::NAME;
        if (!$this->_queryBuilder) {
            $path = $this->_path;
            $workedModel = array_shift($path);
            $model = new $workedModel;
            $modelAdapter = $this->_grid->getModelAdapter();
            if ($modelAdapter) {
                $model->setConnectionService($modelAdapter);
            }
            $this->_queryBuilder = $model->queryBuilder();
            $this->_queryBuilder->columnsJoinOne($path, $name);

            $mainModel = $this->_grid->getContainer()->getDataSource()->getModel();
            $relations = $mainModel->getRelationPath($workedModel);
            if (!$relations) {
                throw new \Engine\Exception("Relations to model '".get_class($model)."' by path '".implode(", ", $path)."' not valid");
            }
            $relation = array_pop($relations);
            $field = $relation->getReferencedFields();
            $this->_queryBuilder->where($field." = :id:");
            if ($this->_count) {
                $this->_queryBuilder->limit($this->_count);
            }
            $this->_queryBuilder->orderBy($this->_orderBy);
        }
        $rows = $this->_queryBuilder->getQuery()->execute(['id' => $id]);
        $values = [];
        foreach ($rows as $row) {
            $values[] = $row->{$name};
        }

        return $values;
    }

    /**
     * Return column value by key
     *
     * @param mixed $row
     * @return string|integer
     */
	public function getValue($row) 
	{
		return $this->render($row);
	}

	/**
	 * Set tag for value.
	 * 
	 * @param string $tag
	 * @return void
	 */
	public function setTag($tag) 
	{
	    $this->_tag = $tag;
	}
	
	/**
	 * Set left and right tag for value.
	 * 
	 * @param string $left
	 * @param string $right
	 * @return \Engine\Crud\Grid\Column\JoinMany
	 */
	public function setLeftRightTag($left, $right) 
	{
	    $this->_left = $left;
	    $this->_right = $right;
	    return $this;
	}
	
	/**
	 * Set empty value
	 * 
	 * @param string $na
	 * @return \Engine\Crud\Grid\Column\JoinMany
	 */
	public function setEmptyValue($na)
	{
		$this->_na = $na;
		return $this;
	}

    /**
     * Return separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }
}