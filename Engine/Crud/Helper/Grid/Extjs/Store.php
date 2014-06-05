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
        $limit = $grid->getLimit();
        $title = $grid->getTitle();
        $action = $grid->getAction();
        $key = $grid->getKey();

        $code = "
        Ext.define('".static::getStoreName()."', {
            extend: 'Ext.ux.crud.Store',
            alias: 'widget.".static::$_module.ucfirst(static::$_prefix)."Store',
            requires: ['Ext.data.proxy.Ajax', 'Ext.ux.crud.Store'],
            model: '".static::getModelName()."',
            pageSize: ".$limit.",
            autoLoad: false,
            remoteSort: true,
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
                    root: '".$key."',
                    totalProperty: 'results'
                },
                writer: {
                    type: 'json',
                    writeAllFields: false,
                    root: '".$key."'
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