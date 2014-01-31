<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid;

use Engine\Crud\Helper\Grid\Extjs\BaseHelper,
    Engine\Crud\Grid\Extjs as Grid;

/**
 * Class html grid helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Extjs extends BaseHelper
{
    /**
     * Is create js file prototype
     * @var boolean
     */
    protected static $_createJs = true;

	/**
	 * Generates a widget to show a html grid
	 *
	 * @param \Engine\Crud\Grid\Extjs $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $title = $grid->getTitle();

        $code = "
        Ext.define('".static::getGridName()."', {
            extend: 'Ext.grid.Panel',
            store: '".static::getStoreName()."',
            alias: 'widget.".static::$_module.ucfirst(static::$_prefix)."Grid',
            ";

        $width = $grid->getWidth();
        if ($width) {
            $code .= "width: ".$width.",
            ";
        }
        $height = $grid->getHeight();
        if ($width) {
            $code .= "height: ".$height.",
            ";
        }

        $code .= "requires: [";
        $editType = $grid->getEditingType();
        $requires = [];
        if ($editType) {
            $requires[] = "'Ext.grid.plugin.".ucfirst($editType)."Editing'";

        }
        $requires[] = "'Ext.form.field.*'";
        $code .= implode(",", $requires);
        $code .= "],
            ";

        $code .= "itemId: '".static::$_module.ucfirst(static::$_prefix)."Grid',";

		return $code;
	}

    /**
     * Return object name
     *
     * @return string
     */
    public static function getName()
    {
        return static::getGridName();
    }

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return "
        });";
    }
}