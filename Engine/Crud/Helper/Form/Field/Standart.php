<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Field;

use Engine\Crud\Form\Field;

/**
 * Class form field helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Standart extends \Engine\Crud\Helper
{
	/**
	 * Generates a widget to show a html grid filter
	 *
	 * @param \Engine\Crud\Form\Field $filter
	 * @return string
	 */
	static public function _(Field $field)
	{
        $code = '<div class="control-group">';

		return $code;
	}

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return '</div>';
    }
}