<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class grid components helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Components extends BaseHelper
{
	/**
	 * Generates grid component object
	 *
	 * @param \Engine\Crud\Grid\Extjs $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $buildStore = $grid->isBuildStore();



        $code = "
            buildStore: ".($buildStore ? 'true' : 'false').",

            initComponent: function() {
                var me = this;

                if (me.buildStore) {
                    Ext.apply(me, {
                        store : me.createStore(me.store)
                    });
                }

                ";

        $editType = $grid->getEditingType();
        if ($editType) {
            $code .= "me.cellEditing = Ext.create('Ext.grid.plugin.".ucfirst($editType)."Editing', {
                    clicksToEdit: 2,
                    listeners: {
                        scope: me,
                        edit: me.onEdit
                    }
                });
                me.plugins = me.cellEditing;";
        }

        $code .= "
                me.plugins = me.cellEditing;
                me.columns = me.columnsGet();
                me.tbar    = me.getTopToolbarItems();
                me.bbar    = me.getBottomToolbarItems();

                me.callParent(arguments);
            },

            ";
        /*        me.on('selectionchange', me.onSelect, this);
                me.on('celldblclick', me.onDbClick, this);
                me.on('cellclick', me.onClick, this);
                me.on('keypress', me.onKeyPress, this);
            },";
        */
        return $code;
	}
}