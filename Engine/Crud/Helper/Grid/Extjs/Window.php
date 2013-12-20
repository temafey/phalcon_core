<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class grid window helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Window extends BaseHelper
{
    /**
     * Is create js file prototype
     * @var boolen
     */
    protected static $_createJs = true;

	/**
	 * Generates grid functions object
	 *
	 * @param \Engine\Crud\Grid\Extjs $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $title = $grid->getTitle();
        $code = "
        Ext.define('".static::getWinName()."', {
            extend: 'Ext.Window',
            itemId: '".static::$_module.ucfirst(static::$_prefix)."Window',
            layout: 'fit',
            items: [
                { xtype: '".static::$_module.ucfirst(static::$_prefix)."Grid', itemId: '".static::$_module.ucfirst(static::$_prefix)."Grid' }
            ]
        });";

        return $code;
	}

    /**
     * Return object name
     *
     * @return string
     */
    public static function getName()
    {
        return static::getWinName();
    }

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return false;
    }
}