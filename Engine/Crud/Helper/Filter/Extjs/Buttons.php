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
                var me = this;

                return [
                    {
                        text: 'Apply',
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