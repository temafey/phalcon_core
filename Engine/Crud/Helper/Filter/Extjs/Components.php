<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Extjs;

use Engine\Crud\Grid\Filter;

/**
 * Class form components helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Components extends BaseHelper
{
	/**
	 * Generates form component object
	 *
	 * @param \Engine\Crud\Grid\Filter $filter
	 * @return string
	 */
	static public function _(Filter $filter)
	{

        $code = "

            initComponent : function() {
                var me = this;

                me.items   = me.fieldsGet();
                me.tbar    = me.tbarGet();
                me.bbar    = me.bbarGet();
                me.buttons = me.buttonsGet();
                me.callParent();
            },";

        return $code;
	}
}