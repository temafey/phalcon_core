<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Field\Standart;

use Engine\Crud\Form\Field;

/**
 * Class grid Form field helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Element extends \Engine\Crud\Helper
{
	/**
	 * Generates a widget to show a html grid Form
	 *
	 * @param \Engine\Crud\Form\Field $Form
	 * @return string
	 */
	static public function _(Field\Field $field)
	{
        $element = $field->getElement();
        if ($field instanceof Field\Submit) {
            $element->setAttribute('class', 'btn');
        }
        $element->setAttribute('id', $field->getKey());
        $element->setAttribute('placeholder', $field->getLabel());
        $code = '<div class="controls">';
        $code .= $element->render();
        $code .= "</div>";

		return $code;
	}
}