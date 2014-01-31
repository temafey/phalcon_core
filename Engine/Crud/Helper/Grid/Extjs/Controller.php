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
            ";
        $code .= "requires: [";
        $code .= "'".static::getStoreLocalName()."',";
        $code .= "'".static::getStoreName()."',";
        $code .= "'".static::getGridName()."',";
        $code .= "'".static::getFormName()."'";
        $code .= "],";
        $code .= "
            init: function(){
                this.storeLocal = this.getStore('".static::getStoreLocalName()."');
                this.store = this.getStore('".static::getStoreName()."');
                this.grid = this.getView('".static::getGridName()."');
                this.form = this.getView('".static::getFormName()."');
                /*this.storeLocal.addListener('load', function(){
                       this._onPingSuccess();
                    }, this);
                this.storeLocal.load();*/
                this.store.load();
                this.activeStore = this.store;
            },
            _onPingSuccess: function(){
                localCnt = this.storeLocal.getCount();

                if (localCnt > 0){
                    for (i = 0; i < localCnt; i++){
                        var localRecord = this.storeLocal.getAt(i);
                        var deletedId   = localRecord.data.id;
                        delete localRecord.data.id;
                        store.add(localRecord.data);
                        localRecord.data.id = deletedId;
                    }
                    this.store.sync();
                    for (i = 0; i < localCnt; i++){
                        this.localStore.removeAt(0);
                    }
                }

                this.store.load();
                this.activeStore = this.store;
            },

            _onPingFailure: function(){
                this.activeStore = this.storeLocal;
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