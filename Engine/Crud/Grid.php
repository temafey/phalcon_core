<?php
/**
 * @namespace
 */
namespace Engine\Crud;

use Engine\Crud\Grid\Column,
    Engine\Crud\Grid\Filter,
    Engine\Crud\Container\Container;

/**
 * Class for manage datas.
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid\Column
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
abstract class Grid implements
    \Phalcon\Events\EventsAwareInterface,
    \Phalcon\DI\InjectionAwareInterface,
    \ArrayAccess,
    \Countable,
    \Iterator,
    \Serializable
{	
	use \Engine\Tools\Traits\DIaware,
		\Engine\Tools\Traits\EventsAware,
		\Engine\Tools\Traits\Resource,
		\Engine\Crud\Tools\Renderer,
        \Engine\Crud\Tools\Attributes;
		
	/**
	 * Default container adapter
	 */
	const DEFAULT_CONTAINER = 'Mysql';

    /**
     * Default container model adapter
     */
    const DEFAULT_MODEL_ADAPTER = 'db';

    /**
     * Default decorator
     */
    const DEFAULT_DECORATOR = 'Standart';
	
	/**
	 * Default param names
	 */
	const DEFAULT_PARAM_SORT_NAME       = 'sort';
	const DEFAULT_PARAM_DIRECTION_NAME  = 'direction';
	const DEFAULT_PARAM_LIMIT_NAME      = 'limit';
	const DEFAULT_PARAM_PAGE_NAME       = 'page';

    const DIRECTION_ASC     = 'asc';
    const DIRECTION_DESC    = 'desc';

    /**
     * Use strict mode for render colum value
     * @var bool
     */
    protected $_strict = true;

	/**
	 * Array of grid columns
	 * @var array
	 */
	protected $_columns = [];

    /**
     * Primary column object
     * @var \Engine\Crud\Grid\Column\Primary
     */
    protected $_primaryColumn;
	
	/**
	 * Data container object
	 * @var \Engine\Crud\Container\Grid\Adapter
	 */
	protected $_container = null;

    /**
     * Container adapter class name
     * @var string
     */
    protected $_containerAdapter = null;

    /**
     * Container container model adapter
     * @var string
     */
    protected $_containerModelAdapter = null;
	
	/**
	 * Container model
	 * 
	 * @var string|object
	 */
	protected $_containerModel = null;
	
	/**
	 * Container condition
	 * @var array|string
	 */
	protected $_containerConditions = null;
		
	/**
	 * Container joins
	 * @var array|string
	 */
	protected $_containerJoins = [];

    /**
     * Grid additionals
     * @var array
     */
    protected $_additionals = [];

	/**
	 * Filter object.
	 * @var \Engine\Crud\Grid\Filter
	 */
	protected $_filter = null;    

	/**
	 * Grid data array
	 * @var array
	 */
	protected $_data = null; 

	/**
	 * Data array position
	 * @var integer
	 */
    protected $_position = 0; 
	
	/**
	 * Grid array params
	 * @var array
	 */
	protected $_params = [];
	
	/**
	 * Array of column names and container clumn keys 
	 * @var array
	 */
	protected $_columnNames = [];
	
	/**
	 * Grid params options
	 */
	protected $_sortParamName = null;
	protected $_sortParamValue = null;
	
	protected $_directionParamName = null;
	protected $_directionParamValue = null;
	
	protected $_limitParamName = null;
	protected $_limitParamValue = null;
		
	protected $_pageParamName = null;
	protected $_pageParamValue = null;
	
	/**
	 * Default grid params
	 * @var array
	 */
	protected $_defaultParams = [
		'sort' => null,
		'direction' => null,
		'page' => 1,
		'limit' => 10
	];

    /**
     * Grid dom id
     * @var string
     */
    protected $_id;
	
	/**
	 * Grid action link
	 * @var string
	 */
	protected $_action;
	
	/**
	 * Grid title
	 * @var string
	 */
	protected $_title;
	
	/**
	 * Form model
	 * @var \Engine\Crud\Form
	 */
	protected $_form = null;
	
	/**
	 * Grid edit action link
	 * @var string
	 */
	protected $_editAction = null;
	
	/**
	 * is exec count query
	 * @var bool
	 */
	protected $_isCountQuery = true;

    /**
     * Limit for using in data container query
     * @var int
     */
    protected $_extraLimit = 100;

	/**
     * Constructor
     *
     * @param mixed $options
     * @return void
     */
	final public function __construct(
        array $params = [],
        \Phalcon\DiInterface $di = null,
        \Phalcon\Events\ManagerInterface $eventsManager = null,
        array $options = []
    ) {
        if ($di) {
            $this->setDi($di);
        }
        if ($eventsManager) {
            $this->setEventsManager($eventsManager);
        }
        if ($options) {
            $this->_setOptions($options);
        }
		$this->_initResource();
		$this->init();
		$this->setParams($params);
		$this->_autoloadInitMethods();
		$this->_autoloadSetupMethods();
	}

    /**
     * Set extra grid options before inititialize
     *
     * @param array $options
     * @return void
     */
    protected function _setOptions(array $options)
    {
        if (isset($options['container'])) {
            $this->_container = $options['container'];
        }
        if (isset($options['model'])) {
            $this->_containerModel = $options['model'];
        }
        if (isset($options['conditions'])) {
            $this->_containerConditions = $options['conditions'];
        }
        if (isset($options['joins'])) {
            $this->_containerJoins = $options['joins'];
        }
        if (isset($options['adapter'])) {
            $this->_containerModelAdapter = $options['adapter'];
        }
    }
	
	/**
     * Initialize grid (used by extending classes)
     *
     * @return void
     */
	public function init()
	{
	}

    /**
     * Initialize container
     *
     * @return void
     */
    protected function _initContainer()
    {
        if (null !== $this->_container) {
            $config = [];
            $config['container'] = $this->_container;
            $config['modelAdapter'] = (null === $this->_containerModelAdapter) ? static::DEFAULT_MODEL_ADAPTER : $this->_containerModelAdapter;
            $config['conditions'] = $this->_containerConditions;
            $config['joins'] = $this->_containerJoins;
            $this->_container = Container::factory($this, $config);
        } else {
            $config = [];
            $config['adapter'] = (null === $this->_containerAdapter) ? static::DEFAULT_CONTAINER : $this->_containerAdapter;
            $config['model'] = $this->_containerModel;
            $config['modelAdapter'] = (null === $this->_containerModelAdapter) ? static::DEFAULT_MODEL_ADAPTER : $this->_containerModelAdapter;
            $config['conditions'] = $this->_containerConditions;
            $config['joins'] = $this->_containerJoins;
            $this->_container = Container::factory($this, $config);
        }
        if (null !== $this->_di) {
            $this->_container->setDi($this->_di);
        }
        if (null !== $this->_eventsManager) {
            $this->_container->setEventsManager($this->_eventsManager);
        }
        if ($this->_filter instanceof Filter) {
            $this->_filter->setContainer($this->_container);
        }
    }


    /**
     * Initialize decorator
     *
     * @return void
     */
    protected function _initDecorator()
    {
        $this->_decorator = static::DEFAULT_DECORATOR;
    }

    /**
     * Initialize form
     *
     * @return void
     */
    protected function _initForm()
    {
        if (!$this->_form) {
            $grid = get_class($this);
            $form = str_replace("\\Grid\\", "\\Form\\", $grid);
            if (class_exists($form) && $this->_form instanceof \Engine\Crud\Form) {
                $this->_form = $form;
            }
        }
        if (is_string($this->_form)) {
            $this->_form = new $this->_form(null, [], $this->_di, $this->_eventsManager);
        }
    }
	
	/**
	 * Initialize columns
	 * 
	 * @return void
	 */
	abstract protected function _initColumns();
	
	/**
	 * Initialize filters
	 * 
	 * @return void
	 */
	abstract protected function _initFilters();
	
	/**
	 * Setup grid
	 * 
	 * @return void
	 */
	protected function _setupGrid()
	{
		foreach ($this->_columns as $key => $column) {
			if (!$column instanceof Column) {
			    throw new \Engine\Exception("Column '".$key."' not instance of Column interface");
			}
            $column->setStrictMode($this->_strict);
            if ($column instanceof Column\Primary) {
                $this->_primaryColumn = $column;
            }
			$column->init($this, $key);
			$key = $column->getKey();
			$name = $column->getName();
			$this->_columnNames[$key] = $name;
		}
	}
	
	/**
	 * Setup container
	 * 
	 * @return void
	 */
	protected function _setupContainer()
	{
	}
	
	/**
	 * Setup filter
	 * 
	 * @return void
	 */
	protected function _setupFilter()
	{
        if ($this->_filter instanceof Filter) {
            $this->_filter->init($this);
        }
	}
	
	/**
	 * Autoload all methods in class with prefix in function name _init
	 * 
	 * @return void
	 */
	private function _autoloadInitMethods()
	{
		$this->setAutoloadMethodPrefix('_init');
		$this->_runResourceMethods();
	}
	
	/**
	 * Autoload all methods in class with prefix in function name _setup
	 * 
	 * @return void
	 */
	private function _autoloadSetupMethods()
	{
		$this->setAutoloadMethodPrefix('_setup');
		$this->_runResourceMethods();
	}
	
	/**
	 * Autoload all methods in class with prefix in function name _update
	 * 
	 * @return void
	 */
	private function _autoloadUpdateMethods()
	{
		$this->setAutoloadMethodPrefix('_update');
		$this->_runResourceMethods();
	}
	
	/**
	 * Set grid data from Container object
	 * 
	 * @return void
	 */
	protected function _setData() 
	{
		$this->_autoloadUpdateMethods();
		foreach ($this->_columns as $column) {
			$column->updateContainer($this->_container);
		}
		$dataSource = $this->_container->getDataSource();
		foreach ($this->_columns as $column) {
			$column->updateDataSource($dataSource);
		}
		
		if ($this->_filter instanceof Filter) {
			$this->_filter->setParams($this->getFilterParams());
			$this->_filter->applyFilters($dataSource);
		}
		$data = $this->_container->getData($dataSource);
		$this->_paginate($data);
	}

    /**
     * Set strict mode
     *
     * @param bool $strict
     * @return \Engine\Crud\Grid\Column\Base
     */
    public function setStrictMode($strict = true)
    {
        $this->_strict = (bool) $strict;
        return $this;
    }

    /**
     * Return container adapter
     *
     * @return \Engine\Crud\Container\Grid\Adapter
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * Return container model
     *
     * @return \Engine\Mvc\Model
     */
    public function getModel()
    {
        return $this->_containerModel;
    }

    /**
     * Return container model adapter
     *
     * @return \Engine\Mvc\Model
     */
    public function getModelAdapter()
    {
        return $this->_containerModelAdapter;
    }

    /**
     * Return grid container joins rules
     *
     * @return array|string
     */
    public function getJoins()
    {
        return $this->_containerJoins;
    }

    /**
     * Return grid container conditions
     *
     * @return array|string
     */
    public function getConditions()
    {
        return $this->_containerConditions;
    }
	
	/**
	 * Return grid fetching data
	 * 
	 * @return array
	 */
	public function getData() 
	{
		if (null === $this->_data) {
			$this->_setData();
		}
		
		return $this->_data;
	}
	
	/**
	 * Return grid columns
	 * 
	 * @return array
	 */
	public function getColumns()
	{
		return $this->_columns;
	}

    /**
     * Return if exist primary grid column
     *
     * @return array
     */
    public function getPrimaryColumn()
    {
        return $this->_primaryColumn;
    }

    /**
     * Return filter
     *
     * @return \Engine\Crud\Grid\Filter
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Return form
     *
     * @return \Engine\Crud\Form
     */
    public function getForm()
    {
        return $this->_form;
    }
	
	/**
	 * Clear grid data
	 * 
	 * @return \Engine\Crud\Grid
	 */
	public function clearData() 
	{
		$this->_data = null;
		return $this;
	}
	
	/**
	 * Return datas with rendered values.
	 * 
	 * @return array
	 */
	public function getDataWithRenderValues()
	{
		if (null === $this->_data) {
			$this->_setData();
		}
		$data = $this->_data;
        $data['data'] = [];
		foreach ($this->_data['data'] as $row) {
            $values = [];
			foreach ($this->_columns as $key => $column) {
                $values[$key] = $column->render($row);
			}
            $data['data'][] = $values;
		}

		return $data;
	}
	
	/**
	 * Return datas with column value
	 * 
	 * @return array
	 */
	public function getColumnData()
	{
		if (null === $this->_data) {
			$this->_setData();
		}
        $data = [];
		foreach ($this->_data['data'] as $i => $row) {
            $values = [];
			foreach ($this->_columns as $key => $column) {
                $values[$key] = $column->getValue($row);
			}
            $data[] = $values;
		}
		
		return $data;
	}

    /**
     * Return data in json format
     *
     * @return string
     */
    public function toJson()
    {
        $data = $this->getDataWithRenderValues();

        return json_encode($this->_data['data']);
    }
	
	/**
	 * Paginate data array
	 * 
	 * @param array $data
	 * @return void
	 */
	protected function _paginate(array $data) 
	{
		$this->_data['data'] = $data['data'];
        unset($data['data']);
		$page = $data['page'];
		$limit = $data['limit'];
		
		if ($page == 'all' || $limit == 'all' || $limit === false) {
			$this->_data['all_count'] = count($data['data']);
			return;
		}
		
		$id_page = $page;			
			
		$mess_num = $limit;
		if ($mess_num == NULL) {
			$mess_num = 10;
		}
		if (!isset($page_num)) {
			$page_num = 11;
		}
		if ($page_num % 2 == 0) {
			$page_num = $page_num + 1;
		}
		
		$lines = ($this->_isCountQuery) ? $data['lines'] : $limit;
		$pages = ($this->_isCountQuery) ? $data['pages'] : 1;
		$mess_now = ($this->_isCountQuery) ? $data['mess_now'] : $limit;
		
		if (!isset($id_page)) {
			$start = 0;
			$pn2 = 1;
		} else {
			$pn2 = $id_page;
			if (is_numeric($pn2) && $pn2 <= $pages && $pn2 >= 1) {
				$start = $pn2 - 1;
			} else {
				$start = 0;
				$pn2 = 1;
			}
		}
		$start = $mess_num * ($start - 1) + $mess_num;
			
		if ($page_num >= $pages) {
			$st1 = 1;
			$st2 = $pages;
		} elseif (($pn2 > (($page_num - 1) / 2)) && ($pn2 < $pages - (($page_num - 1) / 2))) {
			$st1 = $pn2 - (($page_num - 1) / 2);
			$st2 = $pn2 + (($page_num - 1) / 2);
		} elseif (($pn2 >= $pages - (($page_num - 1) / 2)) && ($pn2 <= $pages)) {
			$st1 = $pages - ($page_num - 1);
			$st2 = $pages;
		} else {
			$st1 = 1;
			$st2 = $page_num;
		}
			
		if (($pn2 - 1) >= 1) {
			$prev = ($pn2) - 1;
		} else {
			$prev = 1;
		}
			
		if (($pn2 + 1) <= $pages) {
			$next = ($pn2) + 1;
		} else {
			$next = $pages;
		}
		
		$array_pages = [];
		$array_pages ['first'] = ($prev > 1) ? 1 : 0;

        $array_pages['prev'] = null;
		if ($pn2 != $prev) {
			$array_pages['prev'] = $prev;
		} elseif ($lines != 0) {
			$array_pages['prev'] = 0;
		}
			
		for ($n = $st1; $n <= $st2; $n ++) {
			$array_pages [$n] = ($pn2 == $n) ? 'now' : 1;
		}
			
		$array_pages['next'] = ($next != $pn2 and $lines != 0) ? $next : 0;
		$array_pages['last'] = ($next != $pages) ? $pages : 0;
		
		$this->_data['array_pages'] = $array_pages;
		$this->_data['mess_count'] = $mess_num;
		$this->_data['mess_now'] = $mess_now;
		$this->_data['page_count'] = $page_num;
		$this->_data['all_count'] = $lines;
		$this->_data['all_page'] = $pages;
		$this->_data['page_now'] = $pn2;
	}

    /**
     * Do something before render
     *
     * @return string
     */
    protected function _beforeRender()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
    }

    /**
     * Add grid additional
     *
     * @param string $type
     * @param string $module
     * @param string $key
     * @param string $param
     * @return \Engine\Crud\Grid
     */
    public function addAdditional($type, $module, $key, $param)
    {
        $additional = [
            'type' => $type,
            'module' => $module,
            'key' => $key,
            'param' => $param
        ];
        $this->_additionals[] = $additional;

        return $this;
    }

    /**
     * Return grid additionals
     *
     * @return array
     */
    public function getAdditionals()
    {
        return $this->_additionals;
    }

    /**
     * Return paginate params
     *
     * @return array
     */
    public function getPaginateParams()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
        return $this->_data['array_pages'];
    }

    /**
     * Return count data rows
     *
     * @return integer
     */
    public function getCount()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
        return $this->_data['all_count'];
    }

    /**
     * Return count row on current page
     *
     * @return integer
     */
    public function getCountCurrentPage()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
        return $this->_data['page_count'];
    }

    /**
     * Return count row on current page
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
        return $this->_data['page_now'];
    }

    /**
     * Return count row on current page
     *
     * @return integer
     */
    public function getPages()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
        return $this->_data['array_pages'];
    }
	
	/**
	 * Set count query flag
	 * 
	 * @param bool $flag
	 * @return \Engine\Crud\Grid
	 */
	public function setNoCountQuery($flag = false)
	{
		$this->_isCountQuery = (bool) $flag;
		return $this; 
	}
	
	/**
	 * is execute count query
	 * 
	 * @return bool
	 */
	public function isCountQuery()
	{
		return $this->_isCountQuery;
	}

    /**
     * Is grid data can be edit
     *
     * @return boolean
     */
    public function isEditable()
    {
        foreach ($this->_columns as $column) {
            if ($column->isEditable()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set id param
     *
     * @param string $id
     * @return \Engine\Crud\Grid
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Set grid params
     *
     * @param array $params
     * @return \Engine\Crud\Grid
     */
    public function setParams(array $params)
    {
        $sort = $this->getSortParamName();
        if (isset($params[$sort])) {
            $this->_sortParamValue = $params[$sort];
        }
        $direction = $this->getSortDirectionParamName();
        if (isset($params[$direction])) {
            $this->_directionParamValue = $params[$direction];
        }
        $limit = $this->getLimitParamName();
        if (isset($params[$limit])) {
            $this->_limitParamValue = $params[$limit];
        }
        $page = $this->getPageParamName();
        if (isset($params[$page])) {
            $this->_pageParamValue = $params[$page];
        }
        $this->_params = $params;

        return $this;
    }

    /**
     * Return current sort params
     *
     * @param bool $withFilterParams
     * @return array
     */
    public function getSortParams($withFilterParams = true)
    {
        if (null !== $this->_sortParamValue && isset($this->_columns[$this->_sortParamValue])) {
            return $this->_columns[$this->_sortParamValue]->getSortParams($withFilterParams);
        }
        if ($withFilterParams) {
            return $this->getFilterParams();
        }
        return [];
    }
	
	/**
	 * Set action
	 * 
	 * @param string $action
	 * @return \Engine\Crud\Grid
	 */
	public function setAction($action) 
	{
		$this->_action = $action;
		return $this;
	}
	
	/**
	 * Set title
	 * 
	 * @param string $title
	 * @return \Engine\Crud\Grid
	 */
	public function setTitle($title) 
	{
		$this->_title = $title;
		return $this;
	}
	
	/**
	 * Set sort param
	 * 
	 * @param string $sort
	 * @return \Engine\Crud\Grid
	 */
	public function setSort($sort) 
	{
        $this->_sortParamValue = $sort;
		return $this;
	}

    /**
     * Return default param by name
     *
     * @param string $name
     * @return string
     */
    public function getDefaultParam($name)
    {
        if (!isset($this->_defaultParams[$name])) {
            return false;
        }
        return $this->_defaultParams[$name];
    }

    /**
     * Get id param
     *
     * @return string
     */
    public function getId()
    {
        if (null === $this->_sortParamValue) {
            return $this->_defaultParams['sort'];
        }
        return $this->_sortParamValue;
    }

    /**
     * Return sort param name
     *
     * @return string
     */
    public function getSortParamName()
    {
       return (null !== $this->_sortParamName) ? $this->_sortParamName : static::DEFAULT_PARAM_SORT_NAME;
    }
	
	/**
	 * Get sort param
	 * 
	 * @return string
	 */
	public function getSortKey()
	{
        if (null === $this->_sortParamValue) {			
			return $this->_defaultParams['sort'];
		}
		return $this->_sortParamValue;
	}

    /**
     * Return sort direction param name
     *
     * @return string
     */
    public function getSortDirectionParamName()
    {
        return (null !== $this->_directionParamName) ? $this->_directionParamName : static::DEFAULT_PARAM_DIRECTION_NAME;
    }

	/**
	 * Set direction param
	 * 
	 * @param string $direction
	 * @return \Engine\Crud\Grid
	 */
	public function setSortDirection($direction)
	{
		$this->_directionParamValue = $direction;
		return $this;
	}
	
	/**
	 * Get direction param
	 * 
	 * @return string
	 */
	public function getSortDirection()
	{
		if (null === $this->_directionParamValue) {
			return $this->_defaultParams['direction'];
		}
		return $this->_directionParamValue;
	}

    /**
     * Get direction param
     *
     * @return string
     */
    public function toogleSortDirection()
    {
        $direction = (null === $this->_directionParamValue) ? $this->_defaultParams['direction'] : $this->_directionParamValue;
        return ($direction == static::DIRECTION_DESC) ? static::DIRECTION_ASC : static::DIRECTION_DESC;
    }

    /**
     * Return sort direction param name
     *
     * @return string
     */
    public function getLimitParamName()
    {
        return (null !== $this->_limitParamName) ? $this->_limitParamName : static::DEFAULT_PARAM_LIMIT_NAME;
    }
	
	/**
	 * Set limit param
	 * 
	 * @param int $limit
	 * @return \Engine\Crud\Grid
	 */
	public function setLimit($limit) 
	{
		$this->_limitParamValue = $limit;
		return $this;
	}
	
	/**
	 * Get limit param
	 * 
	 * @return integer
	 */
	public function getLimit() 
	{
		if (null === $this->_limitParamValue) {			
			return $this->_defaultParams['limit'];
		}
		return $this->_limitParamValue;
	}

    /**
     * Return limit that use for store query like base limit
     * @return integer
     */
    public function getExtraLimit()
    {
        $limit = $this->getLimit();
        return ($limit > $this->_extraLimit) ? $limit : $this->_extraLimit;
    }

    /**
     * Return sort direction param name
     *
     * @return string
     */
    public function getPageParamName()
    {
        return (null !== $this->_pageParamName) ? $this->_pageParamName : static::DEFAULT_PARAM_PAGE_NAME;
    }
	
	/**
	 * Set page param
	 * 
	 * @param int $page
	 * @return \Engine\Crud\Grid
	 */
	public function setPage($page) 
	{
		$this->_pageParamValue = $page;
		return $this;
	}
	
	/**
	 * Get page param
	 * 
	 * @return integer
	 */
	public function getPage() 
	{
		if (null === $this->_pageParamValue) {			
			return $this->_defaultParams['page'];
		}
		return $this->_pageParamValue;
	}

	/**
	 * Get grid title
	 * 
	 * @return string
	 */
	public function getTitle() 
	{
		return $this->_title;
	}
	
	/**
	 * Get grid action
	 * 
	 * @return string
	 */
	public function getAction() 
	{
		return $this->_action;
	}
	
	/**
	 * Set param
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return /Engine/Crud/Grid/Grid
	 */
	public function setParam($name, $value)
	{
		$this->_params[$name] = $value;
		return $this;
	}
	
	/**
	 * Set filter param
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return /Engine/Crud/Grid/Grid
	 */
	public function setFilterParam($name, $value)
	{
		$prefix = $this->_filter->getPrefix();
		if (null === $prefix) {
			return $this->setParam($name, $value);
		}
		if (!isset($this->_params[$prefix])) {
			$this->_params[$prefix] = [];
		}
		$this->_params[$prefix][$name] = $value;
		
		return $this;
	}
	
	/**
	 * Return filter params
	 * 
	 * @return array
	 */
	public function getFilterParams()
	{
		$prefix = (null !== $this->_filter) ? $this->_filter->getPrefix() : null;
		if (null === $prefix) {
			$params = $this->_params;
		} elseif (!isset($this->_params[$prefix])) {
            $params = [];
		} else {
            $params = $this->_params[$prefix];
        }
        $normalizeParams = [];
        if (null === $this->_filter) {
            return $params;
        }
        foreach ($this->_filter->getFields() as $field) {
            $key = $field->getKey();
            if (array_key_exists($key, $params) && $params[$key] !== '') {
                $normalizeParams[$key] = $params[$key];
            }
        }

        return $normalizeParams;
	}
	
	/**
	 * Return filter param by name
	 * 
	 * @param string $name
	 * @return string|array|null
	 */
	public function getFilterParam($name)
	{
		$prefix = $this->_filter->getPrefix();
		if (null === $prefix) {
			return (isset($this->_params[$name])) ? $this->_params[$name] : null;
		}
		if (!isset($this->_params[$prefix])) {
			return null;
		}
		return (isset($this->_params[$prefix][$name])) ? $this->_params[$name] : null;
	}
	
	/**
	 * Return grid columns titles.
	 * 
	 * @return array
	 */
	public function getColumnsTitle()
	{
		$titles = [];	
		foreach ($this->_columns as $key => $column) {
			$title = $column->getTitle();
			$titles[$key] = $column;
		}
		
		return $titles;	
	}
	
	/**
	 * Return column by name
	 * 
	 * @param string $name
	 * @return \Engine\Crud\Grid\Column
	 */
	public function getColumnByName($name)
	{
		if (empty($name)) {
			return false;
		}
		foreach ($this->_columns as $column) {
			$columnName = $column->getName();
			if ($columnName == $name) {
				return $column;
			}
		}
		
		return false;
	}
	
    /**
     * Delete rows from grid table by array of primary key values.
     * 
     * @param array|string $ids
     * @return bool|array
     */
	public function deleteAction($ids) 
	{
	    if (!is_array($ids)) {
	        $ids = trim($ids);
	        if ($ids == "") {
	            return false;
	        }	            
	        $ids = [$ids];
	    }	    
	    if (count($ids) == 0) {
	        return false;
	    }
		
		return $this->_container->delete($ids);		
	}
	
	/**
	 * Update column in rows by array of primary key values.
	 * 
	 * @param string $column
	 * @param array|string $ids
	 * @param array $value
	 * @return bool|array
	 */
	public function bulkUpdate($ids, array $data)
	{
		if (!is_array($ids)) {
	        $ids = trim($ids);
	        if ($ids == "") {
	            return false;
	        }	            
	        $ids = [$ids];
	    }	    
	    if (count($ids) == 0) {
	        return false;
	    }
	    
	    $updateData = [];
	    foreach ($data as $key => $value) {	    
		    $column = (isset($this->_columns[$key])) ? $this->_columns[$key] : $this->getColumnByName($key);
		    if ($column === false) {
		        return false;
		    }
	    	$name = $column->getName();
	    	if (null !== $this->_form) {
	    		if (!($field = $this->_form->getFieldByKey($key))) {
	    			$field = $this->_form->getFieldByName($name);
	    		}
	    		$field->setValue($value);
	    		$saveData = $field->getSaveData();
	    		$updateData[$saveData['key']] = $saveData['value']; 
	    	} else {
	    		$updateData[$name] = $value;
	    	}
	    }
	    
		return $this->_container->update($ids, $updateData);
	}
	
	/**
     * Return grid column
     *
     * @param  string $key The form column key.
     * @return \Engine\Crud\Grid\Column
     * @throws \Exception if the $key is not a column in the grid.
     */
    public function __get($key)
    {
        if (!isset($this->_columns[$key])) {
            throw new \Engine\Exception("Column \"$key\" is not in the grid");
        }
        return $this->_columns[$key];
    }
    
    /**
	 * Whether a offset exists
	 * 
	 * @return boolean
     */
    public function offsetExists($offset)
    {
    	return isset($this->_data['data'][$offset]);
    }
    
    /**
	 * Offset to retrieve
	 * 
	 * @return integer|string|array|object|null
     */
    public function offsetGet($offset)
    {
    	return $this->offsetExists($offset) ? $this->_data['data'][$offset] : null;
    }
    
    /**
	 * Offset to set
	 * 
	 * @return void
     */
    public function offsetSet($offset, $value)
    {
    	if (is_null($offset)) {
    		$this->_data['data'][] = $value;
    	} else {
    		$this->_data['data'][$offset] = $value;
    	}
    }
    
    /**
	 * Offset to unset
	 * 
	 * @return void
     */
    public function offsetUnset($offset)
    {
    	unset($this->_data['data'][$offset]);
    }
    
    /**
	 * Rewind the Iterator to the first element
     */
    public function rewind()
    {
    	$this->_position = 0;
    }

    /**
     * Return the current element
     * 
     * @return integer|string|array|object|null
     */
    public function current()
    {
    	return $this->_data['data'][$this->_position];
    }
    
    /**
     * Return the key of the current element
     * 
     * @return integer
     */
    public function key()
    {
    	return $this->_position;
    }
    
    /**
     * Move forward to next element
     * 
     * @return void
     */
    public function next()
    {
    	++$this->_position;
    }
    
    /**
     * Checks if current position is valid
     * 
     * @return boolean
     */
    public function valid()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
    	return isset($this->_data['data'][$this->_position]);
    }
    
    /**
     * Count elements of an object
     * 
     * @return integer
     */
    public function count()
    {
    	return $this->_data['lines'];
    }

    /**
     * String representation of object
     * 
     * @return string
     */
    public function serialize()
    {
    	return serialize($this->_params, $this->_data);
    }
    
    /**
     * Constructs the object
     * 
     * @return void
     */
    public function unserialize($data)
    {
    	list($this->_params, $this->_data) = unserialize($data);
    }
    
    /**
     * array_pop
     */
    public function __invoke(array $data = null)
    {
	    if (is_null($data)) {
	    	return $this->_data;
	    } else {
	    	$this->_data = $data;
	    }
    }
}