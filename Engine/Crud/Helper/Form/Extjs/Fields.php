<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Extjs;

use Engine\Crud\Form\Extjs as Form,
    Engine\Crud\Form\Field as Field;

/**
 * Class form fields helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Fields extends BaseHelper
{
	/**
	 * Generates form fields object
	 *
	 * @param \Engine\Crud\Form\Extjs $form
	 * @return string
	 */
	static public function _(Form $form)
	{
        $code = "
            fieldsGet: function(){
                return [";

        $fields = [];
        foreach ($form->getFields() as $field) {
            if ($field instanceof Field) {
                $fields[] = self::renderField($field);
            }
        }

        $code .= implode(",", $fields);

        $code .= "
                ]
            },";

        return $code;
	}
}