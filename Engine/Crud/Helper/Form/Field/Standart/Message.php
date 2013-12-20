<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Field\Standart;

use Engine\Crud\Form\Field;

/**
 * Class grid Form field message helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Message extends \Engine\Crud\Helper
{
	/**
	 * Generates a widget to show a html grid Form
	 *
	 * @param \Engine\Crud\Form\Field $Form
	 * @return string
	 */
	static public function _(Field $field)
	{
        $code = '';
        $element = $field->getElement();

        //Get any generated messages for the current element
        $messages = $element->getMessages();
        if (count($messages)) {
            //Print each element
            $code .= '<div class="messages">';
            foreach ($messages as $message) {
                $code .= $message;
            }
            $code .= '</div>';
        }

		return $code;
	}
}