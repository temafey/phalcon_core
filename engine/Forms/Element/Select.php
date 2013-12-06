<?php
/**
 * @namespace
 */
namespace Engine\Forms\Element;

/**
 * Class Select
 *
 * @category    Engine
 * @package     Forms
 * @subcategory Element
 */
class Select extends \Phalcon\Forms\Element\Select implements \Engine\Forms\ElementInterface
{
    /**
     * Form element description
     * @var string
     */
    protected $_desc;

    /**
     * @param string $name
     * @param array $options
     * @param array $attributes
     */
    public function __construct($name, $options=null, $attributes=null)
    {
        if (!is_array($options)) {
            $options = [];
        }
        $optionsData = (!empty($options['options']) ? $options['options'] : null);
        unset($options['options']);
        if (!is_array($attributes)) {
            $attributes = [];
        }
        $options = array_merge($options, $attributes);
        parent::__construct($name, $optionsData, $options);
    }

    /**
     * If element is need to be rendered in default layout
     *
     * @return bool
     */
    public function useDefaultLayout()
    {
        return true;
    }

    /**
     * Sets the element description
     *
     * @param string $desc
     * @return \Engine\Forms\Element\Select
     */
    public function setDesc($desc)
    {
        $this->_desc = $desc;
        return $this;
    }


    /**
     * Returns the element's description
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->_desc;
    }
}