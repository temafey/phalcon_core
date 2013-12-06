<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Dojo;

/**
 * Class dojo div helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Div extends \Engine\Crud\Helper\AbstactHelper
{
    /**
     * Generates a widget to show a dojo grid layout
     *
     * @param \Engine\Crud\Grid\AbstractGrid $grid
     * @return string
     */
    static public function _(\Engine\Crud\Grid\AbstractGrid $grid)
    {
        $gridDivId = 'gridDiv_'.$grid->getId();
        $code = '
		<div id="'.$gridDivId.'"></div>
		';

        return $code;
    }
}