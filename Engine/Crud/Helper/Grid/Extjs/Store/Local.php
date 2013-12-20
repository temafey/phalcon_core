<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs\Store;

use Engine\Crud\Grid\Extjs as Grid,
    Engine\Crud\Helper\Grid\Extjs\BaseHelper;

/**
 * Class grid filter helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Local extends BaseHelper
{
    /**
     * Is create js file prototype
     * @var boolen
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
        $code = "
        Ext.define('".static::getStoreLocalName()."', {
            extend: 'Ext.data.Store',
            requires  : ['Ext.data.proxy.LocalStorage'],
            model: '".static::getModelName()."',

            proxy: {
                type: 'localstorage',
                id  : '".static::$_prefix."'
            }
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
        return static::getStoreLocalName();
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