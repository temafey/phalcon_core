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
class Compound extends Standart
{
	protected $elements;

    /**
     * Filter fields for compound
     * @var array
     */
    protected $_fields;

    /**
     * Compound form elements
     * @var array
     */
    private $_elements = [];

    /**
     * Constructor
     *
     * @param string $label
     * @param string $name
     * @param array $fields
     */
    public function __construct($label = null, $name = null, array $fields)
	{
		parent::__construct ($label, $name);
		$this->_fields = $fields;
	}

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    public function _init()
	{
		foreach ( $this->_fields as $key => $field) {
			if (!$field instanceof Field) {
                throw new \Engine\Exception('Compound filter field not instance of Field');
            }
            $field->init($this->_filter, $key);
            if (is_array($this->_default) && isset($this->_default[$key])) {
                $field->setDefault($this->_default[$key]);
            }
		}
	}

    /**
     * Return phalcon form element
     *
     * @return \Phalcon\Forms\Element
     */
    public function getElement()
    {
        if (!empty($this->_elements)) {
            return $this->_elements;
        }
		foreach ($this->_fields as $name => $field) {
			$element = $field->getElement();
			$this->_elements[] = $element;
		}
		
		return $this->_elements;
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
        $valuse = $this->getValue();
		foreach ($this->_fields as $key => $field) {
			$field->setValue($this->_default[$key]);
			$field->applyFilter($dataSource, $container);
		}
		
		return true;
	}

    /**
     * Return datasource filters
     *
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Filter\SearchFilterInterface
     */
    public function getFilter(Container $container)
    {
		return null;
	}

}
