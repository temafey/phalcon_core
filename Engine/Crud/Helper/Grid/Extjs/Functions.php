<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class grid functions helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Functions extends BaseHelper
{
	/**
	 * Generates grid functions object
	 *
	 * @param \Engine\Crud\Grid\Extjs $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $code = "
            tbarGet: function(){
                return[
                    {
                        xtype: 'button',
                        text: 'Add',
                        iconCls: 'icon-add',
                        handler: this._onUserAddClick
                    },
                    {
                        type: 'button',
                        text: 'Remove',
                        iconCls: 'icon-delete',
                        handler: this._onUserDelClick
                    }
                ]
            },

            _onUserAddClick: function(button){
            },

            _onUserDelClick: function(button){
            },";

        return $code;
	}
}