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
        $title = $grid->getTitle();
        $prefix = ucfirst(static::$_prefix);

        $code = "
        Ext.define('".static::getControllerName()."', {
            extend: 'Ext.app.Controller',
            views: ['".$prefix."Win', '".$prefix."Grid'],
            models: ['".$prefix."'],
            stores: ['".$prefix."', '".$prefix."Local'],
            init: function(){
                var storeLocal = this.getStore('".static::getStoreLocalName()."');
                var store = this.getStore('".static::getStoreName()."');
                storeLocal.addListener('load', function(){
                       this._onPingSuccess();
                    }, this);

                storeLocal.load();
            },
            _onPingSuccess: function(){
                var win             = this.getView('".static::getWinName()."').create();
                var localStore      = this.getStore('".static::getStoreLocalName()."');
                var store           = this.getStore('".static::getStoreName()."');
                var grid            = win.getComponent('".static::getGridName()."');

                win.setTitle('".$title."');
                win.show();

                var localCnt = localStore.getCount();

                if (localCnt > 0){
                    for (i = 0; i < localCnt; i++){
                        var localRecord = localStore.getAt(i);
                        var deletedId   = localRecord.data.id;
                        delete localRecord.data.id;
                        store.add(localRecord.data);
                        localRecord.data.id = deletedId;
                    }
                    store.sync();
                    for (i = 0; i < localCnt; i++){
                        localStore.removeAt(0);
                    }
                }

                store.load();
                grid.reconfigure(store);
                grid.store.autoSync = true;
            },

            _onPingFailure: function(){
                var win             = this.getView('".static::getWinName()."').create();
                var localStore      = this.getStore('".static::getStoreLocalName()."');
                var grid            = win.getComponent('".static::getGridName()."');

                win.setTitle('".$title."');
                win.show();
                grid.bbar.bindStore(localStore);
                grid.reconfigure(localStore);
                grid.store.autoSync = true;
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