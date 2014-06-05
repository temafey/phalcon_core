<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class extjs grid controller helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Controller extends BaseHelper
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
        $prefix = ucfirst(static::$_prefix);

        $code = "
        Ext.define('".static::getControllerName()."', {
            extend: 'Ext.app.Controller',
            title: '".$title."',
            baseParams: {},
            ";
        $code .= "requires: [";
        $code .= "'".static::getStoreLocalName()."',";
        $code .= "'".static::getStoreName()."',";
        $code .= "'".static::getGridName()."',";
        $code .= "'".static::getFilterName()."'";
        if ($grid->isEditable()) {
            $code .= ",'".static::getFormName()."'";
        }
        $code .= "],
        ";

        $additionals = [];
        if ($grid->isEditable()) {
            $additionals[] = "
                {
                    type: 'form',
                    controller: '".static::getControllerName()."'
                }";
        }
        foreach ($grid->getAdditionals() as $addional) {
            $additionals[] = "
                {
                    type: '".$addional['type']."',
                    controller: '".ucfirst($addional['module']).'.controller.'.ucfirst($addional['key'])."',
                    param: '".$addional['param']."'
                }";
        }

        $code .= "
            additionals: [".implode(",", $additionals)."
            ],
        ";

        $code .= "
            init: function() {
                var me = this;

                me.storeLocal = this.getStore('".static::getStoreLocalName()."');
                me.store = this.getStore('".static::getStoreName()."');
                me.grid = this.getView('".static::getGridName()."');
                ";
        if ($grid->isEditable()) {
            $code .= "me.form = this.getView('".static::getFormName()."');
        ";
        }
        $code .= "me.filter = this.getView('".static::getFilterName()."');
                me.store.addBaseParams(me.baseParams);
                /*me.storeLocal.addListener('load', function(){
                       me._onPingSuccess();
                    }, me);
                me.storeLocal.load();*/
                me.store.load();
                me.activeStore = me.store;
            },

            _onPingSuccess: function() {
                var me = this;

                localCnt = me.storeLocal.getCount();

                if (localCnt > 0){
                    for (i = 0; i < localCnt; i++){
                        var localRecord = me.storeLocal.getAt(i);
                        var deletedId   = localRecord.data.id;
                        delete localRecord.data.id;
                        store.add(localRecord.data);
                        localRecord.data.id = deletedId;
                    }
                    me.store.sync();
                    for (i = 0; i < localCnt; i++){
                        me.localStore.removeAt(0);
                    }
                }

                me.store.load();
                me.activeStore = this.store;
            },

            _onPingFailure: function() {
                var me = this;

                me.activeStore = me.storeLocal;
            }

        });
        ";

        return $code;
    }

    /**
     * Return object name
     *
     * @return string
     */
    public static function getName()
    {
        return static::getControllerName();
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