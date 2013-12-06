<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

/**
 * class Numeric
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Numeric extends Base
{
	protected function _init()
	{
		$this->_filters = [
			'filter' => 'int'			
		];
	}
}