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
                return [
                    {
                        text: 'Save',
                        scope: this,
                        formBind: true, //only enabled once the form is valid
                        disabled: true,
                        handler: this.onSubmit
                    },
                    {
                        text: 'Reset',
                        scope: this,
                        handler: this.onReset
                    }
                ]
            },
            ";

        return $code;
	}
}