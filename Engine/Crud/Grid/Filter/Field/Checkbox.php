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
class Checkbox extends Standart
{

    /**
     * Element type
     * @var string
     */
    protected $_type = 'check';

    /**
     * Default field value
     * @var mixed
     */
    protected $_value = 1;

    /**
     * Return datasource filters
     *
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Filter\SearchFilterInterface
     */
    public function getFilter(Container $container)
    {
		$values = $this->getValue();
		if ($values) {
			return parent::getFilter($container);
		}

		return false;
	}

    /**
     * Update field
     *
     * return void
     */
    public function updateField()
    {
        $this->_element->getAttributes();
        //$this->_element->setDefault($this->_default);
    }
}