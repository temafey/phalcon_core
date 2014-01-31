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
            extend: 'Ext.data.Store',
            alias: 'widget.".static::$_module.ucfirst(static::$_prefix)."Store',
            requires: ['Ext.data.proxy.Ajax'],
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
            },

            baseParams: null,

            /**
             * add base parameter to store.baseParams
             * @param string key
             * @param string key
             */
            addBaseParamKeyValue: function(key, value){
                var obj = {};
                obj[key] = value;
                Ext.apply(this.baseParams, obj);
            },

            /**
             * add base parameter to store.baseParams
             * @param {Object} key/value object: {key: value}
             */
            addBaseParam: function(obj){
                Ext.apply(this.baseParams, obj);
            },

            /**
             * add several base parameters to store.baseParams
             * @param {Array}: array of key/value object: [{key1: value1, key2: value2,...}]
             */
            addBaseParams: function(objects){
                var me = this;
                if (Ext.isArray(objects)){
                    Ext.each(objects, function(obj){
                        me.addBaseParam(obj);
                    })
                } else if (objects){
                    me.addBaseParam(objects);
                }
            },

            /**
             * reset base parameters
             */
            resetBaseParams: function(){
                this.baseParams = {};
            },

            /**
             * constructor
             * @param {object} config
             */
            constructor: function(config) {
                var me = this;
                // manage base params
                me.baseParams = me.baseParams || {};
                // call parent
                me.callParent(arguments);
            },

            /**
             * override load method to add base params to request params
             * @param {Object} options
             */
            load: function(options){
                var me = this;
                options = options || {};
                options.params = options.params || {};
                Ext.applyIf(options.params, me.baseParams);
                me.callParent([options]);
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