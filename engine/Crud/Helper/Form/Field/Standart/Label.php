<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Field\Standart;

use Engine\Crud\Form\Field\AbstractField as Field;

/**
 * Class grid Form field label helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Label extends \Engine\Crud\Helper\AbstactHelper
{
	/**
	 * Generates a widget to show a html grid Form
	 *
	 * @param \Engine\Crud\Form\Field\AbstractField $Form
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