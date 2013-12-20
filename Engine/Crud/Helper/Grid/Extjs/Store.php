<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class grid filter helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Store extends BaseHelper
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
        $limit = $grid->getLimit();
        $title = $grid->getTitle();
        $action = $grid->getAction();

        $code = "
        Ext.define('".static::getStoreName()."', {
            extend: 'Ext.data.Store',
            alias: 'widget.".static::$_module.static::$_prefix."Store',
            requires: ['Ext.data.proxy.Ajax'],
            model: '".static::getModelName()."',
            pageSize: ".$limit.",
            autoLoad: false,
            proxy: {
                type: 'ajax',
                api: {
                    read:    '".$action."/read',
                    update:  '".$action."/update',
                    create:  '".$action."/create',
                    destroy: '".$action."/delete'
                },
                reader: {
                    type: 'json',
                    root: '".static::$_prefix."',
                    totalProperty: 'results'
                },
                writer: {
                    type: 'json',
                    writeAllFields: false,
                    root: '".static::$_prefix."'
                }
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
        return static::getStoreName();
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