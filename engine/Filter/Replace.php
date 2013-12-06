<?php
/**
 * @namespace
 */
namespace Engine\Filter;

/**
 * Filter Replace
 *
 * @category   Engine
 * @package    Filter
 */
class Replace implements FilterInterface
{

    /**
     * The value being searched for, otherwise known as the needle.
     * An array may be used to designate multiple needles.
     * @param mixed $search
     */
    private $_search;

    /**
     * The replacement value that replaces found search
     * values. An array may be used to designate multiple replacements.
     * @param mixed $replace
     */
    private $_replace;

    public function __construct($search, $replace)
    {
        $this->_search = $search;
        $this->_replace = $replace;
    }

    /**
     * Filter value
     *
     * @param $value
     * @return mixed
     */
    public function filter($value)
    {
        return str_replace($this->_search, $this->_replace, $value);
    }
}