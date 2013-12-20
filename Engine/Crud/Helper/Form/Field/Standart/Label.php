<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Field\Standart;

use Engine\Crud\Form\Field;

/**
 * Class grid Form field label helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Label extends \Engine\Crud\Helper
{
	/**
	 * Generates a widget to show a html grid Form
	 *
	 * @param \Engine\Crud\Form\Field $Form
	 * @return string
	 */
	static public function _(Field $field)
	{
        $label = $field->getLabel();
        $code = '';
        if ($label) {
            $code = '<label class="control-label" for="'.$field->getKey().'">'. $field->getLabel().'</label>';
        }

		return $code;
	}
}