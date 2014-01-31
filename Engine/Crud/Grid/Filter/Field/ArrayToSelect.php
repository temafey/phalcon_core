<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Filter\Field;

use Engine\Filter\SearchFilterInterface as Criteria,
    Engine\Crud\Container\AbstractContainer as Container;

/**
 * Grid filter field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class ArrayToSelect extends Standart
{
	/**
	 * Form element type
	 * @var string
	 */
	protected $_type = 'select';
	
	/**
	 * Select options array
	 * @var array
	 */
	protected $_options;

	/**
	 * Null option
	 * @var string|array
	 */
	protected $_nullOption = -1;
		
	/**
	 * Onchange attribute action 
	 * @var string
	 */
	protected $_onChangeAction = false;

    /**
     * Add options to form element
     * @var bool
     */
    protected $_loadSelectOptions = true;
	
	/**
	 * Constructor
	 *
     * @param string $label
	 * @param string $name
     * @param array $options
	 * @param string $desc
	 * @param string $criteria
	 * @param int $width
	 * @param int $default
	 */
	public function __construct(
        $label = null,
        $name = false,
        array $options = [],
        $desc= null,
        $criteria = Criteria::CRITERIA_EQ,
        $width = 60,
        $default = null
    ) {
		parent::__construct($label, $name, $desc, $criteria, $width, $default);
	    $this->_options = $options;
	}

    /**
     * Update field
     *
     * return void
     */
    public function updateField()
	{
		if ($this->_onChangeAction) {
			$this->_element->setAttribute('onchange', $this->_onChangeAction);
		}

        if ($this->_loadSelectOptions === false) {
            return false;
        }

        $options = $this->getOptions();
        $nullValue = false;
        if ($this->_nullOption) {
            if ($this->_nullOption == -1) {
                $nullValue = -1;
                $null = [-1 => '-'];
            } elseif (is_string($this->_nullOption)) {
                $null = ['' => $this->_nullOption];
                $nullValue = '';
            } elseif (is_array($this->_nullOption)) {
                $null = $this->_nullOption;
                $nullValue = array_keys($this->_nullOption)[0];
            }
            $options = $null + $options;
        }
        $this->_element->setOptions($options);

        $values = $this->getValue();
        if (!$values && $values !== "0") {
            $this->setValue($nullValue);
        }
	}
	
   /**
   	* Return options array
   	*
   	* @return array
  	*/	
	public function getOptions()
	{
		return $this->_options;
	}
	
	/**
	 * Set filter options array
	 * 
	 * @param array $options
	 * @return \Engine\Crud\Grid\Filter\Field
	 */
	public function setOptions(array $options)
	{
	    $this->_options = $options;
	    return $this;
	}
	
	/**
	 * Set nulled select option
	 * 
	 * @param string|array $option
	 * @return \Engine\Crud\Grid\Filter\Field
	 */
	public function setNullOption($option)
	{
		$this->_nullOption = $option;
		return $this;
	}
	
	/**
	 * Set onchange action
	 * 
	 * @param string $onchange
	 * @return \Engine\Crud\Grid\Filter\Field
	 */
	public function setOnchangeAction($onchange)
	{
		$this->_onChangeAction = $onchange;
		return $this;
	}

    /**
     * Return datasource filters
     *
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Filter\SearchFilterInterface
     */
    public function getFilter(Container $container)
    {
        $values = $this->getValue();
        if ($values === null || $values === false || (is_string($values) && $values == "")) {
            return false;
        }

        $filters = [];
        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $val) {
            if (trim($val) == "" || $val == -1 || array_search($val, $this->_exceptionsValues)) {
                continue;
            }
            $filters[] = $container->getFilter('search', [$this->_name => $this->_criteria], $val);
        }
        $filter = $container->getFilter('compound', 'OR', $filters);

        return $filter;
    }

}
