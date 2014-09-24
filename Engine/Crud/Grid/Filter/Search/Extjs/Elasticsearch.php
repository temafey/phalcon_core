<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Filter\Search\Extjs;

use Engine\Crud\Grid\Filter\Search\Elasticsearch as Filter;

/**
 * Class filter grid.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Elasticsearch extends Filter
{
    /**
     * Initialize decorator
     *
     * @return void
     */
    protected function _initDecorator()
    {
        $this->_decorator = 'extjs';
    }

    /**
     * Get grid action
     *
     * @return string
     */
    public function getModulePrefix()
    {
        return $this->_grid->getModulePrefix();
    }

    /**
     * Return extjs module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_grid->getModuleName();
    }

    /**
     * Return extjs grid key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_grid->getKey();
    }

    /**
     * Get grid action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_grid->getAction();
    }
}