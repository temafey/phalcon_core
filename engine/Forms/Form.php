<?php
/**
 * @namespace
 */
namespace Engine\Forms;

/**
 * Class Form
 *
 * @category    Engine
 * @package     Forms
 */
class Form extends \Phalcon\Forms\Form
{
    /**
     * Action methods
     */
    CONST METHOD_GET     = 'get';
    CONST METHOD_POST    = 'post';

    /**
     * Action method
     * @var string
     */
    protected $_method = self::METHOD_GET;

    /**
     * Set from action method
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * Return action form method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

} 