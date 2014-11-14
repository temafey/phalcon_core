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
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column::render()
     * @param mixed $row
     * @return string
     */
    public function render($row)
    {
		if (!isset($row[$this->_key])) {
			 return $this->_na;
		}
		$value = $row[$this->_key];
		if (isset($this->_extraOptions[$value])) {
            $value = $this->_extraOptions[$value];
        }

		return $value ? $value : $this->_na;
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
}