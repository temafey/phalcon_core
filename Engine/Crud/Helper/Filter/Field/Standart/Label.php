<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Field\Standart;

use Engine\Crud\Grid\Filter\Field;

/**
 * Class grid filter field label helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Label extends \Engine\Crud\Helper
{
	/**
	 * Generates a widget to show a html grid filter
	 *
	 * @param \Engine\Crud\Grid\Filter\Field $filter
	 * @return string
	 */
	static public function _(Field $field)
	{
        $code = '<label>'. $field->getLabel().'</label>';
		return $code;
	}
}