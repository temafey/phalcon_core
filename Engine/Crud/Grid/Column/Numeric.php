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
    /**
     * Column type.
     * @var string
     */
    protected $_type = 'int';

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
		$this->_filters = [
			'filter' => 'int'			
		];
	}
}