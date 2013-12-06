<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Field\Standart;

use Engine\Crud\Form\Field\AbstractField as Field;

/**
 * Class grid Form field helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Description extends \Engine\Crud\Helper\AbstactHelper
{
	/**
	 * Generates a widget to show a html grid Form
	 *
	 * @param \Engine\Crud\Form\Field\AbstractField $Form
	 * @return string
	 */
	static public function _(Field $field)
	{
        $desc = $field->getDesc();
        $code = '';
        if ($desc) {
            $code = '<span>'.$field->getDesc().'</span>';
        }

		return $code;
	}
}