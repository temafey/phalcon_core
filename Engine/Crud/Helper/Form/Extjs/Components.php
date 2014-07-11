<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Extjs;

use Engine\Crud\Form\Extjs as Form;

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
	 * @param \Engine\Crud\Form\Extjs $form
	 * @return string
	 */
	static public function _(Form $form)
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