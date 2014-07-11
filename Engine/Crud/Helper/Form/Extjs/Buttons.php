<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Extjs;

use Engine\Crud\Form\Extjs as Form;

/**
 * Class form functions helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Buttons extends BaseHelper
{
	/**
	 * Generates form buttons objects
	 *
	 * @param \Engine\Crud\Form\Extjs $form
	 * @return string
	 */
	static public function _(Form $form)
	{
        $primary = $form->getPrimaryField();
        $key = ($primary) ? $primary->getKey() : false;

        $code = "

            buttonsGet: function(){
                var me = this;

                return [
                    {
                        text: 'Save',
                        scope: me,
                        formBind: true, //only enabled once the form is valid
                        disabled: true,
                        handler: me.onSubmit
                    },
                    {
                        text: 'Reset',
                        scope: me,
                        handler: me.onReset
                    }
                ]
            },
            ";

        return $code;
	}
}