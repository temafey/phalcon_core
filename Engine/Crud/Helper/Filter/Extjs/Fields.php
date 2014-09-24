<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Extjs;

use Engine\Crud\Grid\Filter,
    Engine\Crud\Grid\Filter\Field as Field;

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
	 * @param \Engine\Crud\Grid\Filter $filter
	 * @return string
	 */
	static public function _(Filter $filter)
	{
        $code = "

            fieldsGet: function() {
                return [";

        $fields = [];
        foreach ($filter->getFields() as $field) {
            if ($field instanceof Field) {
                if ($field instanceof Field\Search) {
                    continue;
                }
                if ($field instanceof Field\ArrayToSelect) {
                    $field->setAttrib("autoLoad", false);
                    $field->setAttrib("isLoaded", true);
                    $field->setAttrib("changeListener", true);
                }
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
