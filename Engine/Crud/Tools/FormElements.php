<?php
/**
 * @namespace
 */
namespace Engine\Crud\Tools;

use Phalcon\Forms\Element;

/**
 * Trait FormElements
 *
 * @category    Engine
 * @package     Crud
 * @subcategory Tools
 */
trait FormElements
{
    /**
     * Form element type
     * @var string
     */
    protected $_type;

    /**
     * Container column name
     * @var string
     */
    protected $_name;

    /**
     * Field name
     * @var string
     */
    protected $_key;

    /**
     * Field title
     * @var string
     */
    protected $_label;

    /**
     * Field description
     * @var string
     */
    protected $_desc;

    /**
     * Field width
     * @var integer
     */
    protected $_width;

    /**
     * Field form element
     * @var \Engine\Forms\Element
     */
    protected $_element;

    /**
     * Field value
     * @var string|integer|array
     */
    protected $_value;

    /**
     * Default field value
     * @var mixed
     */
    protected $_default;

    /**
     * Error message after validation
     * @var array
     */
    protected $_errorMessage = '';

    /**
     * Exception values
     * @var array
     */
    protected $_exceptionValues = [];

    /**
     * Set field value
     *
     * @param string|integer|array $value
     * @return \Engine\Crud\Tools\FormElements
     */
    public function setValue($value)
    {
        if ($this->_element instanceof Element) {
            $this->_element->setDefault($value);
            $this->_value = $this->_element->getValue();
        } else {
            $this->_value = $value;
        }

        return $this;
    }

    /**
     * Set default field value
     *
     * @param string|integer|array $value
     * @return \Engine\Crud\Tools\FormElements
     */
    public function setDefault($value)
    {
        $this->_default = $value;
        return $this;
    }

    /**
     * Set exception values
     *
     * @param array $value
     * @return \Engine\Crud\Tools\FormElements
     */
    public function setExceptions(array $values)
    {
        $this->_exceptionValues = $values;
        return $this;
    }

    /**
     * Return field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Return field key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Return field value
     *
     * @return string|array
     */
    public function getValue()
    {
        $value = ((null !== $this->_value) ? $this->_value : $this->_default);
        if ($this->_element instanceof Element) {
            $this->_element->setDefault($value);
            $value = $this->_element->getValue();
            if (null === $value) {
                $value = $this->_element->getDefault();
            }
        }

        return $this->filter($value);
    }

    /**
     * Return field label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Return field description
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->_desc;
    }

    /**
     * Clear field
     *
     * @return \Engine\Crud\Form\Field
     */
    public function clearField()
    {
        $this->_id = null;
        $this->_value = null;
        if ($this->_element instanceof Element) {
            $this->_element->setDefault($this->_default);
        }

        return $this;
    }

    /**
     * Set error message
     *
     * @param  string $message
     * @return \Engine\Crud\Grid\Filter\Field
     */
    public function setErrorMessage($message)
    {
        $this->_errorMessage = (string) $message;
        return $this;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * Return phalcon form element
     *
     * @return \Phalcon\Forms\Element
     */
    public function getElement()
    {
        if (!($this->_element instanceof \Phalcon\Forms\Element)) {
            $this->_createElement();
        }

        return $this->_element;
    }

    /**
     * Create phalcon from element
     *
     * @throws \Engine\Exception
     * @return void
     */
    protected function _createElement()
    {
        if (!($element = $this->getFormElementClassName($this->_type))) {
            throw new \Engine\Exception("Form element '{$this->_type}' not exists");
        }

        $this->_element = new $element($this->_key);
        //$this->_element->setFilters($this->getFilters());
        $this->_element->addValidators($this->getValidators());
        $this->_element->setAttributes($this->getAttribs());
        $this->_element->setLabel($this->_label);
        $this->_element->setDesc($this->_desc);

        $this->_element->setDefault($this->getValue());
        $message = $this->getErrorMessage();
        if ($message) {
            $this->_element->appendMessage($message);
        }
    }

    /**
     * Return form element class name
     *
     * @param string $element
     * @return string
     */
    public function getFormElementClassName($name)
    {
        $element = '\Engine\Forms\Element\\'.ucfirst($name);
        if (!class_exists($element)) {
            $element = '\Phalcon\Forms\Element\\'.ucfirst($name);
            if (!class_exists($element)) {
                return false;
            }
        }

        return $element;
    }
}