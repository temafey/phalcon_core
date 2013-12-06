<?php
/**
 * @namespace
 */
namespace Engine\Forms\Element;

/**
 * Class Hidden
 *
 * @category    Engine
 * @package     Forms
 * @subcategory Element
 */
class Hidden extends \Phalcon\Forms\Element\Hidden implements \Engine\Forms\ElementInterface
{
    /**
     * Form element description
     * @var string
     */
    protected $_desc;

    /**
     * If element is need to be rendered in default layout
     *
     * @return bool
     */
    public function useDefaultLayout(){
        return true;
    }

    /**
     * Sets the element description
     *
     * @param string $desc
     * @return Form_ElementInterface
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