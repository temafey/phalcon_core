<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field,
    Phalcon\Forms\Element;

/**
 * Checkbox field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Checkbox extends Field
{
	/**
	 * Form element type
	 * @var string
	 */
	protected $_type = 'check';
	
	/**
     * Value when checked
     * @var string
     */
	protected $_checkedValue;

    /**
     * Value when not checked
     * @var string
     */
    protected $_uncheckedValue;

    /**
     * Is the checkbox checked?
     * @var bool
     */
	protected $_checked;

    /**
     * @param string $label
     * @param string $name
     * @param bool $checked
     * @param int|string $checkedValue
     * @param int|string $uncheckedValue
     * @param string $desc
     * @param bool $required
     * @param int $width
     * @param int $length
     */
    public function __construct(
        $label = null,
        $name = null,
        $checked = false,
        $checkedValue = 1,
        $uncheckedValue = 0,
        $desc = null,
        $required = false,
        $width = 280,
        $length = 255
    ) {
        parent::__construct($label, $name, $desc, $required, $width, $checked);

        $this->_checked = $checked;
        $this->_checkedValue = $checkedValue;
        $this->_uncheckedValue;
    }

    /**
     * Update field
     *
     * return void
     */
	public function updateField() 
	{
		if ($this->_checked) {
		    $this->setValue($this->getCheckedValue());
		} else {
		    $this->setValue($this->getUncheckedValue());
		}
	}
    
    /**
     * Set value
     *
     * If value matches checked value, sets to that value, and sets the checked
     * flag to true.
     *
     * Any other value causes the unchecked value to be set as the current
     * value, and the checked flag to be set as false.
     *
     *
     * @param  mixed $value
     * @return \Engine\Crud\Form\Field\Checkbox
     */
    public function setValue($value)
    {
        if ($value == $this->getCheckedValue()) {
            $this->_checked = true;
        } else {
            $this->_checked = false;
        }
        if ($this->_element instanceof Element) {
			$this->_element->setDefault($value);
		}
		
        return $this;
    }

    /**
     * Get form field value
     *
     * @return array|string
     */
    public function getValue()
	{
		return (($this->_checked) ? $this->getCheckedValue() : $this->getUncheckedValue());
	}
	
    /**
     * Set checked value
     *
     * @param  string $value
     * @return \Engine\Crud\Form\Field\Checkbox
     */
    public function setCheckedValue($value)
    {
        $this->_checkedValue = (string) $value;
        if ($this->_element instanceof Element) {
			//$this->_element->setCheckedValue($this->getCheckedValue());
		}

        return $this;
    }

    /**
     * Get value when checked
     *
     * @return string
     */
    public function getCheckedValue()
    {
        return $this->_checkedValue;
    }

    /**
     * Set unchecked value
     *
     * @param  string $value
     * @return \Engine\Crud\Form\Field\Checkbox
     */
    public function setUncheckedValue($value)
    {
        $this->_uncheckedValue = (string) $value;
        if ($this->_element instanceof Element) {
			//$this->_element->setUncheckedValue($this->getUncheckedValue());
		}
        return $this;
    }

    /**
     * Get value when not checked
     *
     * @return string
     */
    public function getUncheckedValue()
    {
        return $this->_uncheckedValue;
    }
	
    /**
     * Set checked flag
     *
     * @param  bool $flag
     * @return \Engine\Crud\Form\Field\Checkbox
     */
    public function setChecked($flag)
    {
        $this->_checked = (bool) $flag;
        if ($this->_checked) {
            $this->setValue($this->getCheckedValue());
        } else {
            $this->setValue($this->getUncheckedValue());
        }
        return $this;
    }

    /**
     * Get checked flag
     *
     * @return bool
     */
    public function isChecked()
    {
        return $this->_checked;
    }
}