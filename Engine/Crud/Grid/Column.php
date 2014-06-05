<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid;

use Engine\Crud\Grid;

/**
 * Class abstract grid column.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
abstract class Column implements ColumnInterface
{
    use \Engine\Crud\Tools\Filters,
        \Engine\Crud\Tools\Validators,
        \Engine\Crud\Tools\Attributes;

	const FILTER = 'FILTER';
	
	/**
	 * Container adapter column name.
	 * @var string
	 */
	protected $_name;
    
    /**
     * Column name in grid. 
     * @var string
     */
	protected $_key;
    
	/**
	 * Column title.
	 * @var string
	 */
	protected $_title;
	
	/**
	 * Column type.
	 * @var string
	 */
	protected $_type = 'string';

    /**
	 * Parent grid object.
	 * @var \Engine\Crud\Grid
	 */
	protected $_grid;
	
	/**
	 * Is column sortable
	 * @var bool
	 */
	protected $_isSortable;

    /**
     * Sort direction
     * @var string
     */
    protected $_sortDirection = Grid::DIRECTION_ASC;

	/**
	 * Is column hidden
	 * @var bool
	 */
	protected $_isHidden;

    /**
     * Is column can be editing
     * @var bool
     */
    protected $_isEditable;

    /**
     * Form field key
     * @var string
     */
    protected $_fieldKey;
	
	/**
	 * Column width
	 * @var integer
	 */
	protected $_width;
	
	/**
     * Plugin loaders for filter and validator chains
     * @var array
     */
    protected $_loaders = [];
	
	/**
	 * Action link
	 * @var string
	 */
	protected $_action = null;
	
	/**
	 * Action param name
	 * @var string
	 */
	protected $_actionParam = false;

    /**
     * Use table alias for table field
     * @var bool
     */
    protected $_useTableAlias = true;

    /**
     * Use correlaton name
     * @var bool
     */
    protected $_useCorrelationTableName = false;
	
	/**
	 * Constructor 
	 * 
	 * @param string $title
	 * @param string $name
	 * @param bool $isSortable
	 * @param bool $isHidden
	 * @param int $width
     * @param bool $isEditable
     * @param string $fieldKey
	 */
	public function __construct(
        $title,
        $name = null,
        $isSortable = true,
        $isHidden = false,
        $width = 160,
        $isEditable = true,
        $fieldKey = null
    ) {
		$this->_title = $title;
		$this->_name = $name;
		
		$this->_isSortable = (bool) $isSortable;
		$this->_isHidden = (bool) $isHidden;
        $this->_isEditable = (bool) $isEditable;
        $this->_fieldKey = $fieldKey;
		$this->_width = intval($width);
	}

	/**
	 * Render column
	 * 
	 * @param mixed $row
	 * @return string
	 */
	abstract public function render($row);
    
	/**
	 * Update grid container
	 * 
	 * @param \Engine\Crud\Container\Grid\Adapter $container
	 * @return \Engine\Crud\Grid\Column
	 */
	abstract public function updateContainer(\Engine\Crud\Container\Grid\Adapter $container);
	
	/**
	 * Set grid object and init grid column key.
	 * 
	 * @param \Engine\Crud\Grid $grid
	 * @param string $key
	 * @return \Engine\Crud\Grid\Column
	 */
	public function init(Grid $grid, $key)
	{
		$this->_grid = $grid;
		$this->_key = $key;
		if ($this->_name === null) {
		    $this->_name = $key;
		}
		$this->_init();
        $this->_initFilters();

		return $this;
	}
	
	/**
	 * Update container data source
	 * 
	 * @param mixed $dataSource
	 * @return \Engine\Crud\Grid\Column
	 */
	public function updateDataSource($dataSource)
	{
	}

	/**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
	}

    /**
     * Return column title.
     *
     * @return \Engine\Crud\Grid
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * Return form field
     *
     * @return \Engine\Crud\Form\Field
     */
    public function getField()
    {
        $fieldKey = $this->getFieldKey();
        if (!$form = $this->_grid->getForm()) {
            return false;
        }

        return $form->getFieldByKey($fieldKey);
    }

    /**
     * Returngrid object.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
	/**
	 * Return column render type.
	 * 
	 * @return string
	 */
	public function getType() 
	{
		return $this->_type;
	}
    
	/**
	 * Return column name.
	 * 
	 * @return string
	 */
	public function getName() 
	{
		return $this->_name;
	}
    
	/**
	 * Return key name.
	 * 
	 * @return string
	 */
	public function getKey() 
	{
		return $this->_key;
	}

    /**
     * Return form key name.
     *
     * @return string
     */
    public function getFieldKey()
    {
        return ($this->_fieldKey) ? $this->_fieldKey : $this->getKey();
    }

    /**
     * Set if data can be sort by column
     *
     * @param bool $sortable
     * @return \Engine\Crud\Grid\Column
     */
    public function setSortable($sortable)
    {
        $this->_isSortable = (bool) $sortable;
        return $this;
    }
	
	/**
	 * Check is column sortable.
	 * 
	 * @return bool
	 */
	public function isSortable() 
	{
		return $this->_isSortable;
	}

    /**
     * Set if data by column is hidden
     *
     * @param bool $hidden
     * @return \Engine\Crud\Grid\Column
     */
    public function setHidden($hidden)
    {
        $this->_isHidden = (bool) $hidden;
        return $this;
    }

	/**
	 * Is column hidden.
	 * 
	 * @return bool
	 */
	public function isHidden() 
	{
		return $this->_isHidden;
	}

    /**
     * Set if data by column can be edit
     *
     * @param bool $editable
     * @return \Engine\Crud\Grid\Column
     */
    public function setEditable($editable)
    {
        $this->_isEditable = (bool) $editable;
        return $this;
    }

    /**
     * Is column can be editing
     *
     * @return mixed
     */
    public function isEditable()
    {
        return $this->_isEditable;
    }

    /**
     * Check is column sort
     *
     * @return bool
     */
    public function isSorted()
    {
        return ($this->_grid->getSortKey() == $this->_key) ? true : false;
    }

    /**
	 * Return column width
	 *
	 * @return integer	
	 */
	public function getWidth() 
	{
		return $this->_width;
	}
	
	/**
	 * Return current grid page number.
	 * 
	 * @return integer
	 */
	protected function _getPage() 
	{
		return $this->_grid->getPage();
	}

    /**
     * Return column sort params
     *
     * @param bool $withFilterParams
     * @return array
     */
    public function getSortParams($withFilterParams = true)
    {
        $params = [];
        $sortParamName = $this->_grid->getSortParamName();
        $sortDirectionParamName = $this->_grid->getSortDirectionParamName();
        $params[$sortParamName] = $this->_key;
        $params[$sortDirectionParamName] = $this->toogleSortDirection();

        $limit = $this->_grid->getLimit();
        if ($limit != $this->_grid->getDefaultParam('limit')) {
            $limitParamName = $this->_grid->getLimitParamName();
            $params[$limitParamName] = $limit;
        }

        if ($withFilterParams) {
            $params += $this->_grid->getFilterParams();
        }

        return $params;
    }

	/**
	 * Return current grid sort direction param.
	 * 
	 * @return string
	 */	
	public function getSortDirection()
	{
        if ($this->isSorted()) {
            return $this->_grid->getSortDirection();
        }
		return $this->_sortDirection;
	}

    /**
     * Return current grid sort direction param.
     *
     * @return string
     */
    public function toogleSortDirection()
    {
        if ($this->isSorted()) {
            return $this->_grid->toogleSortDirection();
        }
        return $this->_sortDirection;
    }
	
	/**
	 * Return column value by key
	 * 
	 * @param mixed $row
	 * @return string|integer
	 */
	public function getValue($row)
	{
		$value = $row->{$this->_key};
		$value = $this->filter($value);

		return $value;
	}
	
	/**
	 * Return action for column
	 * 
	 * @return string
	 */
	public function getAction() 
	{
		return $this->_action;
	}

	/**
	 * Return action param name
	 * 
	 * @return string
	 */
	public function getActionParam() 
	{
		return $this->_actionParam;
	}

	/**
	 * Set action to grid column, with post param key name. Set param key name without "=".
	 * 
	 * @param string $action
	 * @param string $actionParam
	 * @return \Engine\Crud\Grid\Column
	 */
	public function setAction($action, $actionParam = false) 
	{
		$this->_action = $action;
		$this->_actionParam = $actionParam;
		
		return $this;
	}

    /**
     * Set flag to add table alias
     *
     * @param boolean $useTableAlias
     * @return \Engine\Crud\Grid\Column
     */
    public function useTableAlias($useTableAlias = true)
    {
        $this->_useTableAlias = $useTableAlias;
        return $this;
    }

    /**
     * Set flag to add correlation table alias
     *
     * @param boolean $useCorrelationTableName
     * @return \Engine\Crud\Grid\Column
     */
    public function useCorrelationTableName($useCorrelationTableName = true)
    {
        $this->_useCorrelationTableName = $useCorrelationTableName;
        return $this;
    }
}