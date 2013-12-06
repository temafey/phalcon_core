<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid;

/**
 * Class html grid helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Filter extends \Engine\Crud\Helper\AbstactHelper
{
	/**
	 * Generates a widget to show a html grid
	 *
	 * @param \Engine\Crud\Grid\AbstractGrid $grid
	 * @return string
	 */
	static public function _(\Engine\Crud\Grid\AbstractGrid $grid)
	{
        $filter = $grid->getFilter();
        return $filter->render();
	}
}