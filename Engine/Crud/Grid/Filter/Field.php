<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Filter;

use Engine\Crud\Grid\Filter,
    Engine\Filter\SearchFilterInterface as Criteria,
    Engine\Crud\Container\AbstractContainer as Container;

/**
 * Grid filter field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
abstract class Field implements FieldInterface
{
    use \Engine\Crud\Tools\Filters,
        \Engine\Crud\Tools\Validators,
        \Engine\Crud\Tools\FormElements,
        \Engine\Crud\Tools\Renderer,
        \Engine\Crud\Tools\Attributes;

	/**
	 * Filter criteria param
	 * @var string
	 */
	protected $_criteria;
	
	/**
	 * Filter
	 * @var \Engine\Crud\Grid\Filter
	 */
	protected $_gridFilter;

    /**
     * @var bool
     */
    protected $_isArray = true;

    /**
     * Exception filter values
     * @var array
     */
    protected $_exceptionsValues = [];
	
	/**
     * Custom error messages
     * @var array
     */
	protected $_errorMessages = [];

    /**
     * Constructor
     *
     * @param string $label
     * @param string $name
     * @param string $desc
     * @param string $criteria
     */
    public function __construct(
        $label = null,
        $name = null,
        $desc = null,
        $criteria = \Engine\Filter\SearchFilterInterface::CRITERIA_EQ
    ) {
        $this->_label = $label;
        $this->_name = $name;
        $this->_desc = $desc;
        $this->_criteria = $criteria;
    }

	/**
	 * Set filter object and init field key.
	 * 
	 * @param \Engine\Crud\Grid\Filter $filter
	 * @param string $key
	 * @return \Engine\Crud\Grid\Filter\Field
	 */
	public function init(\Engine\Crud\Grid\Filter $filter, $key)
	{
		$this->_gridFilter = $filter;
		$this->_key = $key;
		if ($this->_name === null) {
		    $this->_name = $key;
		}
        $this->_init();
        $this->_initFilters();
        $this->_initHelpers();
		
		return $this;
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
     * Initialize field helpers
     *
     * @return void
     */
    protected function _initHelpers()
    {
        $this->setHelpers([
            'standart',
            'standart\Message',
            'standart\Label',
            'standart\Element',
            'standart\Description'
        ]);
    }
	
	/**
	 * Update field
     *
	 * return void
	 */
	public function updateField()
	{
	}

    /**
     * Apply field filter value to dataSource
     *
     * @param mixed $dataSource
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Crud\Grid\Filter\Field
     */
    public function applyFilter($dataSource, Container $container)
    {
        if ($filter = $this->getFilter($container)) {
            $filter->applyFilter($dataSource);
        }

        return $this;
    }

    /**
     * Return filter object
     *
     * @return \Engine\Crud\Grid\Filter
     */
    public function getGridFilter()
    {
        return $this->_gridFilter;
    }

    /**
     * Do something before render
     *
     * @return string
     */
    protected function _beforeRender()
    {
        $this->_element->setAttributes($this->getAttribs());
    }
	
    /**
     * Set error messages
     *
     * @param  array|string $messages
     * @return \Engine\Crud\Grid\Filter\Field
     */
    public function setErrorMessages($messages)
    {
    	$this->_errorMessage = [];
    	if (is_array($messages)) {
    		$messages = array($messages);
    	}
    	foreach ($messages as $message) {
    		$this->_errorMessage = (string) $message;
    	}

        return $this->_errorMessage = $messages;
    }
}