<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Field;

use Engine\Crud\Grid\Filter\Field;

/**
 * Class grid filter field helper
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
	 * @param \Engine\Crud\Grid\Filter\Field $filter
	 * @return string
	 */
	static public function _(Field $field)
	{
        $code = '<tr>';

		return $code;
	}

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return '</tr>';
    }
}