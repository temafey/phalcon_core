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
        $code = "
            initComponent : function() {
                this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 2
                });
                this.plugins = this.cellEditing;
                this.columns = this.columnsGet();
                this.tbar    = this.tbarGet();
                this.bbar    = this.bbarGet();
                this.callParent();
            },";

        return $code;
	}
}