<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

/**
 * Class html grid helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Filter extends BaseHelper
{
	/**
	 * Generates a widget to show a html grid
	 *
	 * @param \Engine\Crud\Grid $grid
	 * @return string
	 */
	static public function _(\Engine\Crud\Grid $grid)
	{
        $filter = $grid->getFilter();
        $filter->render();

        return '';
	}
}