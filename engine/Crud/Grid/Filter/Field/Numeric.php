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
class Numeric extends Standart
{	
	/**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
		parent::_init();
		
		$this->_validators[] = [
            'Numericality'
        ];
	}
}
