<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Extjs;

use Engine\Crud\Grid\Filter;

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
	 * @param \Engine\Crud\Grid\Filter $filter
	 * @return string
	 */
	static public function _(Filter $filter)
	{
        $code = "

            buttonsGet: function(){
                return [
                    {
                        text: 'Apply',
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