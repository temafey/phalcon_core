<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Field;

use Engine\Crud\Grid\Filter\Field\AbstractField as Field;

/**
 * Class grid filter field helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Standart extends \Engine\Crud\Helper\AbstactHelper
{
	/**
	 * Generates a widget to show a html grid filter
	 *
	 * @param \Engine\Crud\Grid\Filter\Field\AbstractField $filter
	 * @return string
	 */
	static public function _(Field $field)
	{
        $code = '';

		return $code;
	}

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return false;
    }
}