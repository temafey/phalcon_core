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
            getTopToolbarItems: function() {
                var me = this,
                    items = [];

                items = [
                    {
                        xtype: 'button',
                        scope: me,
                        text: 'Add',
                        iconCls: 'icon-add',
                        handler: me.onCreate
                    },
                    {
                        type: 'button',
                        scope: me,
                        text: 'Remove',
                        iconCls: 'icon-delete',
                        handler: me.onDelete
                    },
                    '|',
                    'Search: ',{
                        xtype: 'textfield',
                        name: 'searchField',
                        width: 200,
                        listeners: {
                            change: {
                                fn: me.onTextFieldChange,
                                scope: me,
                                buffer: 100
                            }
                        }
                    }
                ];

                return items;
            },
            ";

        return $code;
	}
}