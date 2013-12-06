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
class Standart extends \Engine\Crud\Helper\AbstactHelper
{
	/**
	 * Generates a widget to show a html grid
	 *
	 * @param \Engine\Crud\Grid\AbstractGrid $grid
	 * @return string
	 */
	static public function _(\Engine\Crud\Grid\AbstractGrid $grid)
	{
        $code = '
        <h1>'.$grid->getTitle().'</h1>
        <table id="'.$grid->getId().'" autowidth="true" class="'.$grid->getAttrib('class').' table table-bordered table-hover">';

		return $code;
	}

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return '
        </table>';
    }
}